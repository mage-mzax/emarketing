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


use Mzax_Emarketing_Model_Object_Filter_Customer_Attribute as CustomerAttributeFilter;
use Mzax_Emarketing_Helper_Data as Helper;

/**
 * Class Mzax_Emarketing_Test_Model_Object_Filter_Customer_Name
 *
 * @loadFixture customers
 */
class Mzax_Emarketing_Test_Model_Object_Filter_Customer_Attribute
    extends Mzax_Emarketing_Test_Case_Object_Filter
{
    /**
     * Run name filter that should match the test customer
     *
     * @param array $expected
     * @param array $filterData
     * @param string $currentTime
     *
     * @test
     * @dataProvider filterProvider
     */
    public function testMatchingFilter($expected, $filterData, $currentTime = self::CURRENT_TIME)
    {
        $filter = new CustomerAttributeFilter();
        $filter->addData($filterData);

        $result = $this->runCustomerFilter($filter, $currentTime);

        $this->assertFilterResult($expected, $result, $filter, "No customer found for attribute query.");
    }


    /**
     * @return array
     */
    public function filterProvider()
    {
        // Test Birthday..: 1980-06-01
        // Today..........: 2001-01-01
        return [
            'First name equal John' => [
                [1],
                [
                    'attribute' => 'firstname',
                    'value' => 'John',
                    'operator' => CustomerAttributeFilter::EQUAL
                ]
            ],
            'First name equal Sarah - no match' => [
                [],
                [
                    'attribute' => 'firstname',
                    'value' => 'Sarah',
                    'operator' => CustomerAttributeFilter::EQUAL
                ]
            ],
            'First name is one of John, Sarah, Bob' => [
                [1],
                [
                    'attribute' => 'firstname',
                    'value' => 'John, Sarah, Bob',
                    'operator' => CustomerAttributeFilter::IN
                ]
            ],
            'First name is not one of John, Sarah, Bob' => [
                [],
                [
                    'attribute' => 'firstname',
                    'value' => 'John, Sarah, Bob',
                    'operator' => CustomerAttributeFilter::NOT_IN
                ]
            ],
            'Last name not equal Bob' => [
                [1],
                [
                    'attribute' => 'lastname',
                    'value' => 'Bob',
                    'operator' => CustomerAttributeFilter::NOT_EQUAL
                ]
            ],
            'Gender is male' => [
                [1],
                [
                    'attribute' => 'gender',
                    'value' => '1',
                    'operator' => CustomerAttributeFilter::IN
                ]
            ],
            'Gender is female' => [
                [],
                [
                    'attribute' => 'gender',
                    'value' => '2',
                    'operator' => CustomerAttributeFilter::IN
                ]
            ],

            /*
             * Tests for date specific filters
             */
            'Birthday in 5 to 7 month' => [
                [1],
                [
                    'attribute' => 'dob',
                    'value_from' => 5,
                    'value_to' => 6,
                    'value_unit' => Helper::TIME_UNIT_MONTHS,
                    'relative' => true,
                    'anniversary' => true,
                    'direction' => CustomerAttributeFilter::FUTURE
                ]
            ],
            'Birthday is in 20 to 28 weeks' => [
                [1],
                [
                    'attribute' => 'dob',
                    'value_from' => 20,
                    'value_to' => 22,
                    'value_unit' => Helper::TIME_UNIT_WEEKS,
                    'relative' => true,
                    'anniversary' => true,
                    'direction' => CustomerAttributeFilter::FUTURE
                ]
            ],
            'Birthday was 2 to 4 days ago' => [
                [1],
                [
                    'attribute' => 'dob',
                    'value_from' => 2,
                    'value_to' => 4,
                    'value_unit' => Helper::TIME_UNIT_DAYS,
                    'relative' => true,
                    'anniversary' => true,
                    'direction' => CustomerAttributeFilter::PAST
                ],
                '2001-06-03 10:10:10'
            ],
            'Birthday was 10 to 12 days ago' => [
                [],
                [
                    'attribute' => 'dob',
                    'value_from' => 10,
                    'value_to' => 12,
                    'value_unit' => Helper::TIME_UNIT_DAYS,
                    'relative' => true,
                    'anniversary' => true,
                    'direction' => CustomerAttributeFilter::PAST
                ],
                '2001-06-03 10:10:10'
            ],
            'Birthday is today' => [
                [1],
                [
                    'attribute' => 'dob',
                    'value_from' => 0,
                    'value_to' => 0,
                    'value_unit' => Helper::TIME_UNIT_DAYS,
                    'relative' => true,
                    'anniversary' => true,
                    'direction' => CustomerAttributeFilter::FUTURE
                ],
                '2001-06-01 10:10:10'
            ],
            'Birthday is today - no match' => [
                [],
                [
                    'attribute' => 'dob',
                    'value_from' => 0,
                    'value_to' => 0,
                    'value_unit' => Helper::TIME_UNIT_DAYS,
                    'relative' => true,
                    'anniversary' => true,
                    'direction' => CustomerAttributeFilter::FUTURE
                ],
                '2001-05-01 10:10:10'
            ],
            'Birthday is 1980-06-01' => [
                [1],
                [
                    'attribute' => 'dob',
                    'value' => '1980-06-01',
                    'anniversary' => true
                ]
            ],
        ];
    }
}
