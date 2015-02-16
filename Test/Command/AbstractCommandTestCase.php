<?php

namespace marmelab\NgAdminGeneratorBundle\Test\Command;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @see http://alexandre-salome.fr/blog/Test-your-commands-in-Symfony2
 */
abstract class AbstractCommandTestCase extends WebTestCase
{
    public function runCommand(Client $client, $command)
    {
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $fp = tmpfile();
        $input = new StringInput($command);
        $output = new StreamOutput($fp);

        $application->run($input, $output);

        fseek($fp, 0);
        $output = '';
        while (!feof($fp)) {
            $output = fread($fp, 4096);
        }
        fclose($fp);

        return $output;
    }
}
