<?php
/**
 * The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace OpenNode;

use Exception;
use Mage;
use Mage_CatalogRule_Model_Resource_Rule_Collection;
use Mage_CatalogRule_Model_Rule;
use Mage_SalesRule_Model_Resource_Rule_Collection;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Cache\CleanCommand;
use OpenNode_Bitcoin_Helper_Config as OpenNodeConfig;
use OpenNode_Bitcoin_Helper_Data;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class SetupCommand
 * @package OpenNode
 */
class SetupCommand extends AbstractMagentoCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('opennode:setup');
        $this->setDescription('Setup a test installation of Magento 1.9 with the OpenNode Bitcoin module');
    }

    /**
     * @return bool
     */
    protected function isDemoMode()
    {
        return boolval(Mage::getStoreConfigFlag('design/head/demonotice'));
    }

    /**
     * Disables all existing rules and adds one that applies a sitewide discount
     * @throws Exception
     */
    protected function addSiteWideDiscountRule()
    {
        /** @var Mage_CatalogRule_Model_Resource_Rule_Collection $rules */
        $rules = Mage::getModel('catalogrule/rule')->getCollection();
        $rules->setDataToAll('is_active', false);
        $rules->save();

        /** @var Mage_SalesRule_Model_Resource_Rule_Collection $rules */
        $rules = Mage::getModel('salesrule/rule')->getCollection();
        $rules->setDataToAll('is_active', false);
        $rules->save();

        /** @var Mage_CatalogRule_Model_Rule $rule */
        $rule = Mage::getModel('catalogrule/rule');
        $rule->addData([
            'name' => 'Special Bitcoin Discount',
            'website' => 1,
            'is_active' => 1,
            'stop_rules_processing' => 1,
            'simple_action' => 'by_percent',
            'discount_amount' => '99.0000',
            'sub_is_enable' => 0,
            'actions_serialized' => 'a:4:{s:4:"type";s:34:"catalogrule/rule_action_collection";s:9:"attribute";N;s:8:"operator";s:1:"=";s:5:"value";N;}',
            'conditions_serialized' => 'a:6:{s:4:"type";s:34:"catalogrule/rule_condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}',
            'website_ids' => [1],
            'customer_group_ids' => [0, 1, 2, 4, 5],
        ]);

        $rule->save();
        $rule->applyAll();

        /** @var \Mage_CatalogRule_Model_Flag $flag */
        $flag = Mage::getModel('catalogrule/flag');
        $flag->loadSelf()->setState(0)->save();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function setDevelopmentKey($input, $output)
    {
        /** @var \Mage_Core_Helper_Data $core */
        $core = Mage::helper('core');

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        $question = new Question('<question>What is your OpenNode Development Key (Input is Hidden)?</question> ');
        $question->setHidden(true);

        $key = $core->encrypt($questionHelper->ask($input, $output, $question));
        Mage::app()->getConfig()->saveConfig(OpenNodeConfig::XML_PATH_OPENNODE_DEVELOPMENT_KEY, $key);
        Mage::app()->getConfig()->saveConfig(OpenNodeConfig::XML_PATH_OPENNODE_TEST_MODE, true);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return;
        }

        if (!$this->isDemoMode()) {
            $output->writeln('<warning>Sorry but this setup can only be used in DEMO MODE!</warning>');
            return;
        }

        $this->addSiteWideDiscountRule();
        $this->setDevelopmentKey($input, $output);
    }
}