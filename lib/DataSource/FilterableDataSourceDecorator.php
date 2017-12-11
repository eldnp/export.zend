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

namespace Eldnp\Export\Zend\DataSource;

use Eldnp\Export\Map\AbstractMapDataSource;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class FilterableDataSourceDecorator
 *
 * @package Eldnp\Export\Zend\DataSource
 */
class FilterableDataSourceDecorator extends AbstractMapDataSource
{
    /**
     * @var AbstractMapDataSource
     */
    private $dataSource;

    /**
     * @var InputFilterInterface
     */
    private $inputFilter;

    /**
     * @var ValidationCallbackHandlerInterface
     */
    private $validationCallbackHandler;

    /**
     * @var array
     */
    private $currentData;

    /**
     * FilterableDataSourceDecorator constructor.
     *
     * @param AbstractMapDataSource $dataSource
     * @param InputFilterInterface $inputFilter
     * @param ValidationCallbackHandlerInterface $validationCallbackHandler
     */
    public function __construct(
        AbstractMapDataSource $dataSource,
        InputFilterInterface $inputFilter,
        ValidationCallbackHandlerInterface $validationCallbackHandler
    ) {
        $this->dataSource = $dataSource;
        $this->inputFilter = $inputFilter;
        $this->validationCallbackHandler = $validationCallbackHandler;
    }

    /**
     * @inheritdoc
     */
    protected function currentMap()
    {
        return $this->currentData;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        do {
            $this->currentData = false;
            $this->dataSource->next();
            if (false === $this->dataSource->valid()) {
                return;
            }
            $newData = $this->dataSource->current();
            $this->inputFilter->setData($newData);
            if (!$this->inputFilter->isValid()) {
                $this->validationCallbackHandler->handle($this->inputFilter->getMessages());
                continue;
            }
            $this->currentData = $this->inputFilter->getValues();
            return;
        } while (true);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->dataSource->key();
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->dataSource->valid();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->dataSource->rewind();
    }
}
