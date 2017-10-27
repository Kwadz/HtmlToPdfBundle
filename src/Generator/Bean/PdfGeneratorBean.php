<?php
/**
 * @package Plugin
 */
namespace Jihel\Plugin\HtmlToPdfBundle\Generator\Bean;

use Symfony\Bridge\Twig\TwigEngine;
use Jihel\Plugin\HtmlToPdfBundle\Generator\Exception as GeneratorException;

/**
 * Class PdfGeneratorBean
 *
 * @author Joseph LEMOINE <lemoine.joseph@gmail.com>
 * @link http://www.joseph-lemoine.fr
 * @abstract
 */
abstract class PdfGeneratorBean
{
    const OPTION_PORTRAIT = 0;
    const OPTION_LANDSCAPE = 1;

    /**
     * @var \Symfony\Bridge\Twig\TwigEngine
     */
    protected $templating;

    /**
     * @var array
     */
    protected $commands;

    /**
     * @var array
     */
    protected $constants = array();

    /**
     * @var int
     */
    protected $dpi;

    /**
     * @var string
     */
    protected $tmpFolder;

    /**
     * @var string
     */
    protected $tmpPrefix;

    /**
     * @var bool
     */
    protected $useXvfb;

    /**
     * @var bool
     */
    protected $quietMode;

    /**
     * @var string
     */
    protected $lastCreateCommandResult;

    /**
     * @var string
     */
    protected $lastConcatenateCommandResult;

    /**
     * @param TwigEngine $templating
     * @param array $commands
     * @param array $constants
     * @param int $dpi
     * @param string $tmpFolder
     * @param string $tmpPrefix
     * @param bool $useXvfb
     * @param bool $quietMode
     */
    public function __construct(
        TwigEngine $templating,
        array $commands,
        array $constants,
        $dpi,
        $tmpFolder,
        $tmpPrefix,
        $useXvfb,
        $quietMode
    ) {
        $this->templating = $templating;
        $this->commands   = $commands;
        $this->constants  = $constants;
        $this->dpi        = $dpi;
        $this->tmpFolder  = $tmpFolder;
        $this->tmpPrefix  = $tmpPrefix;
        $this->useXvfb    = $useXvfb;
        $this->quietMode  = $quietMode;
    }

    /**
     * @param string $command
     * @return string
     * @throws \Jihel\Plugin\HtmlToPdfBundle\Generator\Exception\CommandNotFoundException
     */
    protected function getCommand($command)
    {
        if (!isset($this->commands[$command])) {
            throw new GeneratorException\CommandNotFoundException(sprintf(
                'Command "%s" not found', $command
            ));
        }
        return $this->commands[$command];
    }

    /**
     * @param string $command
     * @return string
     */
    protected function getArgs($command)
    {
        return isset($this->commands[$command.'_args']) ? $this->commands[$command.'_args'] : '';
    }

    /**
     * @return string
     */
    protected function getTmpFolder()
    {
        return $this->tmpFolder;
    }

    /**
     * @return string
     */
    protected function getTmpPrefix()
    {
        return $this->tmpPrefix;
    }

    /**
     * @return int
     */
    protected function getDpi()
    {
        return $this->dpi;
    }

    /**
     * @return boolean
     */
    protected function getUseXvfb()
    {
        return $this->useXvfb;
    }

    /**
     * @return boolean
     */
    protected function getQuietMode()
    {
        return $this->quietMode;
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return $this->constants;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function render($template, array $data = array())
    {
        return $this->templating->render($template, $data);
    }

    /**
     * @param string $extension Without dot
     * @param string $mode
     * @return \SplFileObject
     */
    protected function createTemporaryFile($extension, $mode = 'w+')
    {
        return new \SplFileObject(
            $this->getTemporaryFileName($extension),
            $mode
        );
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function getTemporaryFileName($extension)
    {
        return $this->getTmpFolder().DIRECTORY_SEPARATOR
            .$this->getTmpPrefix()
            .date('YmdHis_').uniqid()
            .'.'.$extension
        ;
    }

    /**
     * @param \SplFileObject $tmpHTMLFile
     * @param \SplFileObject $tmpPDFFile
     * @param integer $options
     * @return string
     */
    protected function buildGeneratePdfCommand(\SplFileObject $tmpHTMLFile, \SplFileObject $tmpPDFFile, $options = '')
    {
        $cmd = '';
        // Prepare xvfb
        if ($this->getUseXvfb()) {
            $cmd .= sprintf('%s %s ',
                $this->getCommand('xvfb'),
                $this->getArgs('xvfb')
            );
        }

        // Add wkhtmltopdf
        $cmd .= sprintf('%s --dpi %d %s %s',
            $this->getCommand('wkhtmltopdf'),
            $this->getDpi(),
            $this->getArgs('wkhtmltopdf'),
            $this->buildOptions($options)
        );

        // Add file's path
        $cmd = sprintf('%s %s %s ',
            trim($cmd),
            $tmpHTMLFile->getRealPath(),
            $tmpPDFFile->getRealPath()
        );

        // Quiet execution, output to null
        if ($this->getQuietMode()) {
            return sprintf('%s1> /dev/null 2> /dev/null', $cmd);
        }

        return $cmd;
    }

    /**
     * Add all options as a string
     *
     * @param integer $options
     * @return string
     */
    protected function buildOptions($options)
    {
        $out = [];
        if ($options & self::OPTION_LANDSCAPE) {
            $out[] = '-O landscape';
        }

        return implode(' ', $out);
    }

    /**
     * Build the command without the output
     *
     * @param array $files
     * @param \SplFileObject $tmpPDFFile
     * @return string
     * @throws \Jihel\Plugin\HtmlToPdfBundle\Generator\Exception\NotASplFileObjectException
     */
    protected function buildConcatenatePdfCommand(array $files, \SplFileObject $tmpPDFFile)
    {
        // Prepare pdftk
        $cmd = sprintf('%s %s ',
            $this->getCommand('concatenate'),
            $this->getArgs('concatenate')
        );

        $handler = ord('A');
        $catArgs = array();

        foreach ($files as $file) {
            if (!($file instanceof \SplFileObject)) {
                throw new GeneratorException\NotASplFileObjectException(
                    'An element in array is not a SplFileObject'
                );
            }

            // Combine references to path
//            $catArgs[] = chr($handler).'1';
            $cmd .=  sprintf(' %s=%s', chr($handler++), $file->getRealPath());
        }

        return sprintf('%s output %s', $cmd, $tmpPDFFile->getRealPath());
    }

    /**
     * @return string
     */
    public function getLastConcatenateCommandResult()
    {
        return $this->lastConcatenateCommandResult;
    }

    /**
     * @return string
     */
    public function getLastCreateCommandResult()
    {
        return $this->lastCreateCommandResult;
    }
}
