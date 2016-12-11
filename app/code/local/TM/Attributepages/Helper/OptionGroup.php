<?php

class TM_Attributepages_Helper_OptionGroup extends Mage_Core_Helper_Abstract
{
    /**
     * @param string $word Option title
     * @return string
     */
    public function getGroup($word)
    {
        if (function_exists('mb_strtolower')) {
            $word = mb_strtolower($word);
        }
        if (function_exists('mb_substr')) {
            return mb_substr($word, 0, 1, 'UTF-8');
        }
        return substr($word, 0, 1);
    }

    /**
     * @param string $group1
     * @param string $group2
     */
    public function isSameGroup($group1, $group2)
    {
        if (is_numeric($group1) && is_numeric($group2)) {
            return true;
        } else {
            return $group1 == $group2;
        }
    }

    public function getGroupCssClass($group)
    {
        return $this->getAnchorHref($group);
    }

    /**
     * @param string $group
     * @return string
     */
    public function getAnchorHref($group)
    {
        if (is_numeric($group)) {
            return 'num';
        }
        return $group;
    }

    /**
     * @param string $group
     * @return string
     */
    public function getAnchorTitle($group)
    {
        if (is_numeric($group)) {
            return '#';
        }
        return $group;
    }
}
