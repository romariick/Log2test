<?php

namespace Log2Test\Parser\Log;


use Kassner\LogParser as KassnerLogParser;
use Log2Test\Constants;
use Log2Test\Parser\ConfigParser;


class Apache2LogParser extends LogParser
{
    /**
     * Apache LogParser
     *
     * @var KassnerLogParser
     */
    protected $kassnerLogParser;

    /**
     * @var
     */
    protected $logFormat;

    /*
     * {@inheritDoc}
     */
    public function __construct(ConfigParser $configParser, \SplFileObject $splFile)
    {
        parent::__construct($configParser, $splFile);
        $this->setLogFormat($configParser->getValueFromCache('logFormat'));
        $this->setKassnerLogParser(new \Kassner\LogParser\LogParser($this->getLogFormat()));
    }

    /*
     * {@inheritDoc}
     */
    public function parseOneLine($line)
    {
        $hostConfig = $this->getHostConfig();
        $hostSource = $hostConfig[Constants::HOST_SOURCE];
        $kassnerParser = $this->getKassnerLogParser();
        $parsedLine = $kassnerParser->parse($line);
        if (isset($parsedLine->host) && isset($parsedLine->request) && $parsedLine->host === $hostSource) {
            $requestConfig = explode(\Log2Test\Constants::SPACE_CHAR, $parsedLine->request);
            $path = $requestConfig[\Log2Test\Constants::REQUEST_PATH];
            $method = $requestConfig[\Log2Test\Constants::REQUEST_METHOD];
            $parsedUrl = parse_url($path);
            $extension = pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);
            if (\Log2Test\Constants::METHOD_GET === $method
                && (in_array($extension, $this->getExtensionsAllowed()) ||
                    in_array('*', $this->getExtensionsAllowed()) )) {
                $this->addTestToConfiguration($parsedLine->host, $path);
            }
        }
    }


    /**
     * @return KassnerLogParser
     */
    public function getKassnerLogParser()
    {
        return $this->kassnerLogParser;
    }

    /**
     * @param \Kassner\LogParser\LogParser $kassnerLogParser
     */
    public function setKassnerLogParser($kassnerLogParser)
    {
        $this->kassnerLogParser = $kassnerLogParser;
    }

    /**
     * @return mixed
     */
    public function getLogFormat()
    {
        return $this->logFormat;
    }

    /**
     * @param mixed $logFormat
     */
    public function setLogFormat($logFormat)
    {
        $this->logFormat = $logFormat;
    }
}
