<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListDomainsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:domains:list')
            ->setDescription('List domains of URLs owned by a user')
            ->addArgument('username', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $user = $this->getContainer()->get('fos_user.user_provider.username')->loadUserByUsername($username);
        $domains = $this->getContainer()->get('app.url_manager')->getDomains($user);
        $table = new Table($output);
        $table
            ->setHeaders(['Domain', 'Count'])
            ->setRows($domains)
            ->render();
    }
}
