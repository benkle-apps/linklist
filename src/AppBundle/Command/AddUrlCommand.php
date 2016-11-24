<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddUrlCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:url:add')
            ->setDescription('Add URL for user')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('url', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $url = $input->getArgument('url');

        $user = $this->getContainer()->get('fos_user.user_provider.username')->loadUserByUsername($username);

        $this->getContainer()->get('app.url_manager')->addUrl($user, $url);

        return 0;
    }
}
