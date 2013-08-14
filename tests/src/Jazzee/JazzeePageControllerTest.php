<?php
namespace Jazzee;

/**
 * 
 */
class JazzeePageControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var JazzeePageController
     */
    protected $object;

    protected function setUp()
    {
        
        $_SERVER['SERVER_PORT'] = 'SERVER_PORT';
        $_SERVER['SERVER_NAME'] = 'SERVER_NAME';
        $_SERVER['REQUEST_METHOD'] = 'REQUEST_METHOD';
        $_SERVER['REQUEST_URI'] = 'REQUEST_URI';
        $_SERVER['SERVER_PROTOCOL'] = 'SERVER_PROTOCOL';
        $_SERVER['HTTP_REFERER'] = 'HTTP_REFERER';
        $_SERVER['HTTP_USER_AGENT'] = 'HTTP_USER_AGENT';
        $this->object = new JazzeePageController;
    }

    /**
     * @covers Jazzee\JazzeePageController::path
     */
    public function testPath()
    {
        $testPath = 'path' . uniqid();
        assertThat($this->object->path($testPath), endsWith($testPath));
    }

    /**
     * @covers Jazzee\JazzeePageController::absolutePath
     */
    public function testAbsolutePath()
    {
        $testPath = 'path' . uniqid();
        assertThat($this->object->absolutePath($testPath), endsWith($testPath));
    }

    /**
     * @covers Jazzee\JazzeePageController::getMessages
     */
    public function testGetMessages()
    {
        assertThat(is(emptyArray($this->object->getMessages())));
    }

    /**
     * @covers Jazzee\JazzeePageController::getNavigation
     */
    public function testGetNavigation()
    {
        assertThat($this->object->getNavigation(), is(false));
    }

    /**
     * @covers Jazzee\JazzeePageController::getConfig
     */
    public function testGetConfig()
    {
        assertThat($this->object->getConfig(), anInstanceOf('\Jazzee\Configuration'));
    }

    /**
     * @covers Jazzee\JazzeePageController::log
     * @todo   Implement testLog().
     */
    public function testLog()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Jazzee\JazzeePageController::handleError
     */
    public function testHandleError()
    {
        $message = 'test message' . uniqid();
        $this->setExpectedException('Exception', "Jazzee caught a PHP error: {$message} in file at line line");
        $this->object->handleError(\E_USER_WARNING, $message, 'file', 'line');
    }

    /**
     * @covers Jazzee\JazzeePageController::handleException
     * @todo   Implement testHandleException().
     */
    public function testHandleException()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Jazzee\JazzeePageController::getCache
     * @todo   Implement testGetCache().
     */
    public function testGetCache()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Jazzee\JazzeePageController::getServerPath
     * @todo   Implement testGetServerPath().
     */
    public function testGetServerPath()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}
