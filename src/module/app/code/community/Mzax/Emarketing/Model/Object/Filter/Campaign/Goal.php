<?php
/**
 * Mzax Emarketing (www.mzax.de)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this Extension in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class Mzax_Emarketing_Model_Object_Filter_Campaign_Goal
 *
 * @method $this setAction(string $value)
 * @method $this setOffsetValue(int $value)
 * @method $this setOffsetUnit(string $value)
 */
class Mzax_Emarketing_Model_Object_Filter_Campaign_Goal
    extends Mzax_Emarketing_Model_Object_Filter_Abstract
{
    const ACTION_CLICKED  = 'clicked';
    const ACTION_VIEWED   = 'viewed';
    const ACTION_RECEIVED = 'recieved';

    /**
     * @param Mzax_Emarketing_Model_Object_Filter_Component $parent
     *
     * @return bool
     */
    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return $parent->getQuery()->hasAllBindings('recipient_id', 'goal_time', 'recipient_sent_at');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Goal | Occurred after recipient sent/viewed/click campaign";
    }

    /**
     * @param Mzax_Emarketing_Db_Select $query
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $action = $this->getDataSetDefault('action');

        switch ($action) {
            case self::ACTION_CLICKED:
            case self::ACTION_VIEWED:
                switch ($action) {
                    case self::ACTION_CLICKED:
                        $eventType = Mzax_Emarketing_Model_Recipient::EVENT_TYPE_CLICK;
                        break;
                    case self::ACTION_VIEWED:
                    default:
                        $eventType = Mzax_Emarketing_Model_Recipient::EVENT_TYPE_VIEW;
                        break;
                }
                $query->joinTable(
                    array('recipient_id', 'event_type' => $eventType),
                    'recipient_event',
                    'event'
                );
                $query->group();

                $eventTime = '`event`.`captured_at`';
                $timeLimit = $this->getTimeExpr('offset', $eventTime);

                $query->where("{goal_time} < $timeLimit");
                $query->where("{goal_time} > $eventTime");
                break;

            default:
                $timeLimit = $this->getTimeExpr('offset', '{recipient_sent_at}');
                $query->where("{goal_time} < $timeLimit");
                break;

        }
        $query->group();
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);

        $collection->addField('goal_time');
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);

        $grid->addColumn('goal_time', array(
            'header'   => $this->__('Goal Time'),
            'type'     => 'datetime',
            'index'    => 'goal_time'
        ));
    }

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        return $this->__(
            "If goal occurred no later then %s after recipient %s the campaign.",
            $this->getTimeHtml('offset'),
            $this->getSelectElement('action')->toHtml()
        );
    }

    /**
     * @return string[]
     */
    public function getActionOptions()
    {
        return array(
            self::ACTION_VIEWED   => $this->__('viewed'),
            self::ACTION_CLICKED  => $this->__('clicked'),
            self::ACTION_RECEIVED => $this->__('recieved'),
        );
    }
}
