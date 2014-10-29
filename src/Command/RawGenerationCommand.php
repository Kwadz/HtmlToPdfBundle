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
 * Class RawGenerationCommand
 *
 * @author Joseph LEMOINE <lemoine.joseph@gmail.com>
 * @link http://www.joseph-lemoine.fr
 */
class RawGenerationCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('jihel:pdf:generate-raw-pdf')
            ->setDescription('Generate a pdf from twig html template without any parameters')
            ->setDefinition(array(
                new InputArgument('template', InputArgument::REQUIRED, 'Template name'),
                new InputArgument('savePath', InputArgument::REQUIRED, 'Save file to'),
            ))
            ->setHelp(<<<EOT
The <info>jihel:pdf:generate-raw-pdf</info> Generate a pdf from twig html template without any parameters.

There is a test template that can be used:
JihelPluginHtmlToPdfBundle::exemple.html.twig

Give an absolute save path

<comment>php app/console jihel:pdf:generate-raw-pdf</comment> [template] [savePath]

EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set locale to avoid any localisation problem in template
        $translator = $this->getContainer()->get('translator');
        $translator->setLocale($this->getContainer()->getParameter('locale'));
        $template = $input->getArgument('template');
        $savePath = $input->getArgument('savePath');

        $output->writeln(sprintf('Create file from template "%s"', $template));

        /** @var PdfGenerator $pdfGenerator */
        $pdfGenerator = $this->getContainer()->get('jihel.plugin.html_to_pdf.generator.pdf');
        $file = $pdfGenerator->create($template);

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
        if (!$input->getArgument('template')) {
            $val = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please give a template name:',
                function($val) {
                    if (empty($val)) {
                        throw new \Exception('Template can not be empty');
                    }

                    return $val;
                }
            );
            $input->setArgument('template', $val);
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
