<?php

namespace App\Model\Category;

class Collection extends \Core\Db\ORM\Collection\Collection
{


    /**
     * @return array
     */
    public function buildTree(): array
    {
        $sql = sprintf(
            'SELECT 
                c.*,
                COUNT(p2c.product_id) AS product_count
            FROM `category` c
            LEFT JOIN `product_to_category` p2c 
                ON c.category_id = p2c.category_id
            GROUP BY c.category_id
            ORDER BY c.name;'
        );
        $items = [];
        $tree  = [];

        foreach ($this->fetchAll($sql) as $row) {
            $row = $row->dto()->toArray();

            $row['children'] = [];
            $items[$row['category_id']] = $row;
        }

        foreach ($items as $id => &$item) {
            if ($item['parent_id'] === null) {
                $tree[$id] = &$item;
            } else {
                $parentId = $item['parent_id'];
                if (isset($items[$parentId])) {
                    $items[$parentId]['children'][$id] = &$item;
                }
            }
        }

        return $tree;
    }
}
