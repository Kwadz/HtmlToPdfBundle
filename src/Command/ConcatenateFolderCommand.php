<?php
/**
 * @package Plugin
 */
namespace Jihel\Plugin\HtmlToPdfBundle\Command;

use Jihel\Plugin\HtmlToPdfBundle\Generator\PdfGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConcatenateFolderCommand
 *
 * @author Joseph LEMOINE <lemoine.joseph@gmail.com>
 * @link http://www.joseph-lemoine.fr
 */
class ConcatenateFolderCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('jihel:pdf:concatenate-folder')
            ->setDescription('Concatenate all pdf in a folder')
            ->setDefinition(array(
                new InputArgument('folderPath', InputArgument::REQUIRED, 'Folder name'),
                new InputArgument('savePath', InputArgument::REQUIRED, 'Save file to'),
            ))
            ->setHelp(<<<EOT
The <info>jihel:pdf:concatenate-folder</info> Concatenate all pdf in the given folder.

Give an absolute save path

<comment>php app/console jihel:pdf:concatenate-folder</comment> [folderPath] [savePath]

EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $folderPath = $input->getArgument('folderPath');
        $savePath = $input->getArgument('savePath');

        $output->writeln(sprintf('Create a single file from templates in "%s"', $folderPath));

        /** @var PdfGenerator $pdfGenerator */
        $pdfGenerator = $this->getContainer()->get('jihel.plugin.html_to_pdf.generator.pdf');
        $file = $pdfGenerator->concatenate($folderPath);

        try {
            $output->writeln(sprintf('Save file to "%s"', $savePath));
            rename($file->getRealPath(), $savePath);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error when trying to rename file to "%s" : %s</error>', $savePath, $e->getMessage()));
        }
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('folderPath')) {
            $val = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please give an absolute folder path:',
                function($val) {
                    if (empty($val)) {
                        throw new \Exception('Folder path can not be empty');
                    }

                    return $val;
                }
            );
            $input->setArgument('folderPath', $val);
        }
        if (!$input->getArgument('savePath')) {
            $val = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please give a save path:',
                function($val) {
                    if (empty($val)) {
                        throw new \Exception('Save path can not be empty');
                    }

                    return $val;
                }
            );
            $input->setArgument('savePath', $val);
        }
    }
}
