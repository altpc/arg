<?php

require_once 'abstract.php';

/**
 * Templates Master Prolabels Shell Script
 *
 */
class Mage_Shell_Prolabels extends Mage_Shell_Abstract
{
    /**
     * ProLabels process object
     *
     * @var TM_ProLabels_Model_Indexer
     */
    protected $_prolabels;

    /**
     * Get prolabel indexer object
     *
     * @return TM_ProLabels_Model_Indexer
     */
    protected function _getProlabels()
    {
        if ($this->_prolabels === null) {
            $this->_prolabels = Mage::getModel('prolabels/indexer');
        }
        return $this->_prolabels;
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        if (isset($this->_args['reindex'])) {
            try {
                $this->_getProlabels()->run();
                echo "Prolabels reindexing successfully finished\n";
            } catch (Mage_Core_Exception $e) {
                echo $e->getMessage() . "\n";
            } catch (Exception $e) {
                echo "Reindexing unknown error:\n\n";
                echo $e . "\n";
            }
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f prolabels.php -- [options]

  reindex       Run Prolabels Indexer Process
  help          This help

USAGE;
    }
}

$shell = new Mage_Shell_Prolabels();
$shell->run();
