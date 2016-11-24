<?php

namespace AppBundle\Command;

use AppBundle\Service\GetUrlsOptions;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListUrlsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:url:list')
            ->setDescription('List URLs owned by a user')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('domain', InputArgument::OPTIONAL, '', null)
            ->addOption('offset', null, InputOption::VALUE_OPTIONAL, 0)
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 0)
            ->addOption('gone', null, InputOption::VALUE_NONE)
            ->addOption('not-gone', null, InputOption::VALUE_NONE)
            ->addOption('visited', null, InputOption::VALUE_NONE)
            ->addOption('not-visited', null, InputOption::VALUE_NONE)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        $options = new GetUrlsOptions();

        $options->domain = $input->getArgument('domain');
        $options->offset = intval($input->getOption('offset'), 10);
        $options->limit = intval($input->getOption('limit'), 10);

        $gone = $input->getOption('gone');
        $notGone = $input->getOption('not-gone');
        $visited = $input->getOption('visited');
        $notVisited = $input->getOption('not-visited');

        if ($gone && !$notGone) {
            $options->gone = GetUrlsOptions::GONE_ONLY;
        }

        if (!$gone && $notGone) {
            $options->gone = GetUrlsOptions::GONE_NOT;
        }

        if ($visited && !$notVisited) {
            $options->visited = GetUrlsOptions::VISITED_ONLY;
        }

        if (!$visited && $notVisited) {
            $options->visited = GetUrlsOptions::VISITED_NOT;
        }

        $user = $this->getContainer()->get('fos_user.user_provider.username')->loadUserByUsername($username);
        $urls = $this->getContainer()->get('app.url_manager')->getUrls($user, $options);

        foreach ($urls as $url) {
            $output->writeln($url->getUrl());
        }
    }
}
