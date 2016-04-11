<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP\Util;

/*
 * Class to manage pagination info in all kind of access to domains.
 */

class Pagination
{
    protected $current_page;
    protected $total_items = 0;
    protected $total_pages = 0;
    protected $items_per_page;

    public function __construct($current_page = 1, $items_per_page = 1)
    {
        $this->current_page = $current_page < 1 ? 1 : $current_page;
        $this->items_per_page = $items_per_page < 1 ? 1 : $items_per_page;
    }

    public function getCurrentPage()
    {
        return $this->current_page;
    }

    public function getItemsPerPage()
    {
        return $this->items_per_page;
    }

    public function getTotalPages()
    {
        return $this->total_pages;
    }

    public function getTotalItems()
    {
        return $this->total_items;
    }

    public function getCurrentOffset()
    {
        return (($this->current_page - 1) * $this->items_per_page);
    }

    public function setTotalItems($value)
    {
        $this->total_items = $value;
        if ($this->items_per_page > 0) {
            $this->total_pages = ceil($this->total_items / $this->items_per_page);
        }
    }
}
