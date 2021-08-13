<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vertex\Tax\Model\WsdlCache;

class ExecuteWsdlCache extends Command
{
    /** @var WsdlCache */
    private $wsdlCache;

    public function __construct(WsdlCache $wsdlCache, ?string $name = null)
    {
        $this->wsdlCache = $wsdlCache;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDefinition([])
            ->setName('vertex:tax:warm-wsdl-cache')
            ->setDescription('Execute WSDL Cache Warming');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadWsdl();
        $output->writeln('Finished');
        return Cli::RETURN_SUCCESS;
    }

    private function loadWsdl()
    {
        $this->wsdlCache->load();
    }
}
