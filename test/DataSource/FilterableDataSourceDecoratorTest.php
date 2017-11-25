<?php
/*
 * This file is part of eldnp/export.zend.
 *
 * Eldnp/export.zend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Eldnp/export.zend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with eldnp/export.zend. If not, see <http://www.gnu.org/licenses/>.
 *
 * @see       https://github.com/eldnp/export.zend for the canonical source repository
 * @copyright Copyright (c) 2017 Oleg Verevskoy <verevskoy@gmail.com>
 * @license   https://github.com/eldnp/export.zend/blob/master/LICENSE GNU GENERAL PUBLIC LICENSE Version 3
 */

namespace EldnpTest\Export\Zend\DataSource;

use Eldnp\Export\Map\DataSource\ArrayMapDataSource;
use Eldnp\Export\Zend\DataSource\FilterableDataSourceDecorator;
use Eldnp\Export\Zend\DataSource\ValidationCallbackHandlerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Between;

class FilterableDataSourceDecoratorTest extends TestCase
{
    public function testFilterableDataSourceIterator()
    {
        $validationCallbackHandlerProphecy = $this->prophesize('\Eldnp\Export\Zend\DataSource\ValidationCallbackHandlerInterface');
        $validationCallbackHandlerProphecy
            ->__call('handle', array(Argument::any()))
            ->shouldBeCalledTimes(3)
        ;
        /** @var ValidationCallbackHandlerInterface $validationCallbackHandler */
        $validationCallbackHandler = $validationCallbackHandlerProphecy->reveal();

        $field1Input = new Input('field-1');
        $field1Input->getValidatorChain()->attach(
            new Between(array(
                'min' => 0,
                'max' => 3
            ))
        );

        $field2Input = new Input('field-2');
        $field2Input->getValidatorChain()->attach(
            new Between(array(
                'min' => 3,
                'max' => 7
            ))
        );

        $inputFilter = new InputFilter();
        $inputFilter->add($field1Input);
        $inputFilter->add($field2Input);

        $arrayMapDataSource = new ArrayMapDataSource(array(
            array(
                'field-1' => 1, //valid
                'field-2' => 4, //valid
            ),
            array(
                'field-1' => 4, //not valid
                'field-2' => 6, //valid
            ),
            array(
                'field-1' => 3, //valid
                'field-2' => 2, //not valid
            ),
            array(
                'field-1' => 7, //not valid
                'field-2' => 2, //not valid
            ),
            array(
                'field-1' => 3, //valid
                'field-2' => 5, //valid
            ),
        ));
        $filterableDataSource = new FilterableDataSourceDecorator($arrayMapDataSource, $inputFilter, $validationCallbackHandler);
        $counter = 0;
        foreach ($filterableDataSource as $key => $value) {
            $counter++;
        }
        $this->assertEquals(2, $counter);
    }
}
