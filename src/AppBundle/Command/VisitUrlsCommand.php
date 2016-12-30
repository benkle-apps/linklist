<?php

namespace AppBundle\Command;

use AppBundle\Entity\Url;
use AppBundle\Entity\User;
use AppBundle\Service\GetUrlsOptions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VisitUrlsCommand extends ContainerAwareCommand
{
    private function assert($stmt, $throwable)
    {
        if (!$stmt) {
            throw $throwable;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('app:url:visit')
             ->addArgument('limit', InputArgument::OPTIONAL, 'Number of checked urls', 5)
             ->addArgument(
                 'delta-ratio',
                 InputArgument::OPTIONAL,
                 'Interval for full cycle and cron call, e.g. check everything in 2 hours, cron runs every 5 minutes: 2H/5M',
                 null
             )
             ->addOption('gone', null, InputOption::VALUE_NONE, 'Check gone urls')
             ->addOption('all', null, InputOption::VALUE_NONE, 'Check all urls')
             ->setDescription('Cycle through five stored URLs and check if they are gone');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $urlManager =
            $this->getContainer()
                 ->get('app.url_manager');
        /** @var Client $guzzle */
        $guzzle =
            $this->getContainer()
                 ->get('m6web_guzzlehttp');

        $options                 = new GetUrlsOptions();
        $options->order          = GetUrlsOptions::ORDER_BY_VISITED;
        $options->orderDirection = GetUrlsOptions::ORDER_UP;

        if ($input->getOption('all')) {
            $options->gone = GetUrlsOptions::GONE_BOTH;
        } elseif ($input->getOption('gone')) {
            $options->gone = GetUrlsOptions::GONE_ONLY;
        } else {
            $options->gone = GetUrlsOptions::GONE_NOT;
        }

        $ratio = $input->getArgument('delta-ratio');
        try {
            $this->assert(isset($ratio), new \InvalidArgumentException('No ratio'));
            $ratio = explode('/', $ratio, 2);
            $this->assert(count($ratio) == 2, new \Exception('No proper split, ratio should be something like 2H/5M'));
            $reference = new \DateTimeImmutable();
            $fullCycleInterval = new \DateInterval('PT' . $ratio[0]);
            $fullCycleInterval = $reference->add($fullCycleInterval)->getTimestamp() - $reference->getTimestamp();
            $cronInterval = new \DateInterval('PT' . $ratio[1]);
            $cronInterval = $reference->add($cronInterval)->getTimestamp() - $reference->getTimestamp();
            $this->assert($fullCycleInterval > $cronInterval, new \Exception(sprintf('Full cycle interval (%s) should be significantly larger than cron interval (%s)', $ratio[0], $ratio[1])));
            $ratio = (float)$cronInterval / $fullCycleInterval;
            $count = $urlManager->countUrls(new User(), $options);
            $options->limit = max($input->getArgument('limit'), round($count * $ratio));
        } catch (\InvalidArgumentException $e) {
            $options->limit = intval($input->getArgument('limit'), 10);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return -1;
        }

        /** @var Url[] $urls */
        $urls = $urlManager->getUrls(new User(), $options);

        foreach ($urls as $url) {
            $url->setVisited();
            if ($url->getDomain() !== 'local') {
                try {
                    $output->write($this->truncPad($url->getUrl(), 100));
                    $output->write(' ');
                    $guzzle->head($url->getUrl());
                    $url->setGone(false);
                    $output->writeln('<info>Okay</info>');
                } catch (RequestException $e) {
                    $url->setGone(true);
                    $output->writeln('<fg=red>Gone</>');
                }
            }
            $urlManager->storeUrl($url);
        }
    }

    private function truncPad($string, $length = 50)
    {
        if (strlen($string) > $length) {
            return substr($string, 0, $length - 3) . '...';
        } else {
            return str_pad($string, $length);
        }
    }
}
