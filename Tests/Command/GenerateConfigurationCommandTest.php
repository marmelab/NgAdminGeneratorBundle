<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Command;

use marmelab\NgAdminGeneratorBundle\Test\Command\AbstractCommandTestCase;

class GenerateConfigurationCommandTest extends AbstractCommandTestCase
{
    public function testExecute()
    {
        $client = self::createClient();
        $output = $this->runCommand($client, 'ng-admin:configuration:generate');

        $this->assertEquals(file_get_contents(__DIR__.'/expected/config.js'), $output);
    }
}
