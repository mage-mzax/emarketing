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


use Mzax_Emarketing_Model_Object_Filter_Customer_Name as CustomerNameFilter;


/**
 * Class Mzax_Emarketing_Test_Model_Object_Filter_Customer_Name
 *
 * @loadFixture customers
 */
class Mzax_Emarketing_Test_Model_Object_Filter_Customer_Name
    extends Mzax_Emarketing_Test_Case_Object_Filter
{
    /**
     * Run name filter that should match the test customer
     *
     * @param string $name
     * @param string $operator
     *
     * @return void
     * @test
     * @dataProvider matchingNamesProvider
     */
    public function testMatchingFilter($name, $operator)
    {
        $filter = new CustomerNameFilter();
        $filter->setName($name);
        $filter->setNameOperator($operator);

        $result = $this->runCustomerFilter($filter);

        $this->assertEquals([1], $result, "Customer should have been found for: name $operator '$name' ");
    }

    /**
     * Run name filter that should not match the test customer
     *
     * @param string $name
     * @param string $operator
     *
     * @return void
     * @test
     * @dataProvider noneMatchingNamesProvider
     */
    public function testNonMatchingFilter($name, $operator)
    {
        $filter = new CustomerNameFilter();
        $filter->setName($name);
        $filter->setNameOperator($operator);

        $result = $this->runCustomerFilter($filter);

        $this->assertEquals([], $result, "Customer should not have been found for: name $operator '$name' ");
    }

    /**
     * All name filters bellow should match
     *
     * @return array
     */
    public function matchingNamesProvider()
    {
        return [
            '[empty]' => ['', CustomerNameFilter::LIKE],
            'John' => ['John', CustomerNameFilter::LIKE],
            'Snow' => ['Snow', CustomerNameFilter::LIKE],
            'John Snow' => ['John Snow', CustomerNameFilter::EQUAL],
            'IN(John Snow)' => ['John Snow', CustomerNameFilter::IN],
            'now' => ['now', CustomerNameFilter::LIKE],
        ];
    }

    /**
     * All name filters bellow should not match
     *
     * @return array
     */
    public function noneMatchingNamesProvider()
    {
        return [
            '[empty]' => ['', CustomerNameFilter::EQUAL],
            'Bob' => ['Bob', CustomerNameFilter::LIKE],
            'Sarah' => ['Sarah', CustomerNameFilter::LIKE],
            'JohnSnow' => ['JohnSnow', CustomerNameFilter::LIKE],
            'John' => ['JohnSnow', CustomerNameFilter::EQUAL],
        ];
    }
}
