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

require_once 'abstract.php';

/**
 * Class OpenNode_Bitcoin
 */
class OpenNode_Bitcoin extends Mage_Shell_Abstract
{
    /**
     *
     */
    public function run()
    {
        $isDemoMode = Mage::getStoreConfigFlag('design/head/demonotice');
        if (!$isDemoMode) {
            print 'Your store is NOT in DEMO MODE' . PHP_EOL;
            return;
        }

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

        Mage::getModel('catalogrule/rule')->applyAll();
        Mage::getModel('catalogrule/flag')->loadSelf()->setState(0)->save();
    }
}

$shell = new OpenNode_Bitcoin();
$shell->run();
