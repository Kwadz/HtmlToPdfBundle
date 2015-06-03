<?php
/**
 * @package Plugin
 */
namespace Jihel\Plugin\HtmlToPdfBundle\Generator;

/**
 * Class PdfGenerator
 *
 * @author Joseph LEMOINE <lemoine.joseph@gmail.com>
 * @link http://www.joseph-lemoine.fr
 */
class PdfGenerator extends Bean\PdfGeneratorBean
{
    /**
     * Create a .pdf file from the given $template name with given $data parameters
     *
     * @param string $template
     * @param array $data
     * @return \SplFileObject
     */
    public function create($template, array $data = array())
    {
        // Add constants to data
        $data = array_merge($data, $this->getConstants());

        // Create html
        $tmpHTMLFile = $this->createTemporaryFile('html');
        $tmpHTMLFile->fwrite($this->render($template, $data));

        // Create pdf
        $tmpPDFFile = $this->createTemporaryFile('pdf');
        $cmd = $this->buildGeneratePdfCommand($tmpHTMLFile, $tmpPDFFile);
        $this->lastCreateCommandResult = shell_exec($cmd);

        // Remove tmp html file
        unlink($tmpHTMLFile->getRealPath());
        return $tmpPDFFile;
    }

    /**
     * Create a .pdf file from the given $template name with given $data parameters
     *
     * @param string $view
     * @return \SplFileObject
     */
    public function createFromString($view)
    {
        // Add constants to data
        // Create html
        $tmpHTMLFile = $this->createTemporaryFile('html');
        $tmpHTMLFile->fwrite($view);

        // Create pdf
        $tmpPDFFile = $this->createTemporaryFile('pdf');
        $cmd = $this->buildGeneratePdfCommand($tmpHTMLFile, $tmpPDFFile);
        $this->lastCreateCommandResult = shell_exec($cmd);

        // Remove tmp html file
        unlink($tmpHTMLFile->getRealPath());
        return $tmpPDFFile;
    }

    /**
     * Concatenate the given pdf files.
     * If you provide a path a folder,
     * concatenate them by alphabetic order
     *
     * @param \SplFileObject[]|string $files
     * @param bool $unlink
     * @return \SplFileObject
     * @throws Exception\NotASplFileObjectException
     */
    public function concatenate($files, $unlink = false)
    {
        if (!is_array($files)) {
            if (is_dir($files)) {
                // iterate to get all pdf files, and create an array of \SplFileObject
                /** @var \DirectoryIterator[]|\DirectoryIterator $iterator */
                $iterator = new \DirectoryIterator($files);
                $files = array();
                foreach ($iterator as $file) {
                    if (!$file->isDot() && !$file->isDir() && $file->getExtension() == 'pdf') {
                        $files[$file->getRealPath()] = new \SplFileObject($file->getRealPath());
                    }
                }
                ksort($files);
                $files = array_values($files);
            } else {
                throw new Exception\NotASplFileObjectException(sprintf(
                    'Can\'t find any files in folder "%s"', $files
                ));
            }
        }

        // Create pdf
        $tmpPDFFile = $this->createTemporaryFile('pdf');
        $cmd = $this->buildConcatenatePdfCommand($files, $tmpPDFFile);
        $this->lastConcatenateCommandResult = shell_exec($cmd);

        if ($unlink) {
            foreach ($files as $file) {
                unlink($file->getRealPath());
            }
        }

        return $tmpPDFFile;
    }
}
