<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Command;

use marmelab\NgAdminGeneratorBundle\Test\Command\AbstractCommandTestCase;

class GenerateConfigurationCommandTest extends AbstractCommandTestCase
{
    public function testExecute()
    {
        $client = self::createClient();
        $output = $this->runCommand($client, 'ng-admin:configuration:generate');

        $this->assertContains('NgAdminConfigurationProvider.configure(admin);', $output);
    }
}
