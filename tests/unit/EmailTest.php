<?php
namespace Elabftw\Elabftw;

use PDO;

class EmailTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->Email = new Email(new Config);
    }

    public function testGetMailer()
    {
        $this->assertInstanceOf('Swift_Mailer', $this->Email->getMailer());
    }
}
