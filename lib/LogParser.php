<?php

namespace Log2Test;


use TwigGenerator\Builder\Generator;
use Log2Test\Utils;

abstract class LogParser implements LogParserInterface
{

    /**
     * log file path
     *
     * @var string
     */
    protected $logFile;

    /**
     * list of host to keep from log file
     *
     * @var array
     */
    protected $hosts;

    /**
     * begin parsing at Line X
     *
     * @var int
     */
    protected $beginLine;

    /**
     * Number of line to parse
     *
     * @var int
     */
    protected $numberOfLine;


    /**
     * list of host to keep from log file
     *
     * @var array
     */
    protected $browsers;

    /**
     * list of allowed extension
     *
     * @var array
     */
    protected $extensions_allowed;


    /**
     * Global Test Configuration Array
     * Contains all urls by host
     *
     * @var array
     */
    protected $testConfiguration = [];


    public function __construct()
    {
        $this->setLogFile(ConfigParser::getValueFromKey('logFile'));
        $this->setHosts(ConfigParser::getValueFromKey('hosts'));
        $this->setBeginLine(ConfigParser::getValueFromKey('beginLine'));
        $this->setNumberOfLine(ConfigParser::getValueFromKey('numberOfLine'));
        $this->setBrowsers(ConfigParser::getValueFromKey('browsers'));
        $this->setExtensionsAllowed(ConfigParser::getValueFromKey('extensions_allowed'));
    }

    /**
     * {@inheritDoc}
     */
    public function parse()
    {
        $hosts = $this->getHosts();
        foreach ($hosts as $host) {
            $this->testConfiguration[$host] = [];
        }
        $file = new \SplFileObject($this->getLogFile());
        $file->seek($this->getBeginLine());
        for ($i = 0; !$file->eof() && $i < $this->getNumberOfLine(); $i++) {
            $this->parseOneLine($file->current());
            $file->next();

        }
        $this->generateAllTests();
    }


    public function generateAllTests()
    {
        foreach ($this->getTestConfiguration() as $host => $paths) {
            $hostCleaned = ucfirst(Utils::urlToString($host));
            $builder = new TemplateBuilder();
            $builder->setOutputName($hostCleaned . 'Test.php');
            $builder->setVariable('className', $hostCleaned . 'Test');
            $generator = new Generator();
            $generator->setTemplateDirs(array(
                __DIR__ . '/../templates',
            ));
            $generator->setMustOverwriteIfExists(true);
            $generator->setVariables(array(
                'extends'       => 'PHPUnit_Extensions_SeleniumTestCase',
                'host'          => $host,
                'hostCleaned'   => $hostCleaned,
                'paths'         => $paths,
                'browsers'      => $this->getBrowsers()
            ));
            $generator->addBuilder($builder);
            $generator->writeOnDisk(__DIR__.'/../generated');
        }
    }

    /**
     * {@inheritDoc}
     */
    public abstract function parseOneLine($line);

    /**
     * {@inheritDoc}
     */
    public function addTestToConfiguration($host, $completePath)
    {
        if (!in_array($completePath, $this->testConfiguration[$host])) {
            $this->testConfiguration[$host][] = urlencode($completePath);
        }
    }

    /********** GETTER AND SETTERS ************/

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param string $logFile
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * @return array
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @param array $hosts
     */
    public function setHosts($hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @return int
     */
    public function getBeginLine()
    {
        return $this->beginLine;
    }

    /**
     * @param int $beginLine
     */
    public function setBeginLine($beginLine)
    {
        $this->beginLine = $beginLine;
    }

    /**
     * @return int
     */
    public function getNumberOfLine()
    {
        return $this->numberOfLine;
    }

    /**
     * @param int $numberOfLine
     */
    public function setNumberOfLine($numberOfLine)
    {
        $this->numberOfLine = $numberOfLine;
    }

    /**
     * @return array
     */
    public function getBrowsers()
    {
        return $this->browsers;
    }

    /**
     * @param array $browsers
     */
    public function setBrowsers($browsers)
    {
        $this->browsers = $browsers;
    }

    /**
     * @return array
     */
    public function getTestConfiguration()
    {
        return $this->testConfiguration;
    }

    /**
     * @param array $testConfiguration
     */
    public function setTestConfiguration($testConfiguration)
    {
        $this->testConfiguration = $testConfiguration;
    }

    /**
     * @return array
     */
    public function getExtensionsAllowed()
    {
        return $this->extensions_allowed;
    }

    /**
     * @param array $extensions_allowed
     */
    public function setExtensionsAllowed($extensions_allowed)
    {
        $this->extensions_allowed = $extensions_allowed;
    }
}



