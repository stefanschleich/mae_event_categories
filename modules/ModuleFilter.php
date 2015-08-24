<?php

namespace MaeEventCategories;
use Contao\Database;
use Contao\Input;


class ModuleFilter extends \Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_mae_event_filter';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### MODULE EVENT FILTER ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Compile the current element
     */
    protected function compile()
    {
        $allowAllCats = false;
        $selectedCat = Input::get('category');
        $this->Template->selectedCategory = empty($selectedCat) ? "all" : $selectedCat;
        $this->Template->showAllHref = $this->addToUrl('category=all');

        $items = array();
        $filterCats = empty($this->event_categories) ? array() : unserialize($this->event_categories);
        if(count($filterCats) == 0) {
            if($this->mae_event_list > 0) {
                // take category list from event list, if configured
                $objListCats = $this->Database->prepare("SELECT event_categories FROM tl_module WHERE id=?")->execute($this->mae_event_list);
                if($objListCats->numRows == 1) {
                    $listCats = $objListCats->event_categories;
                    if(!empty($listCats)) {
                        $filterCats = unserialize($listCats);
                    }
                }
            }
            if(count($filterCats) == 0) {
                // take all categories, because there are no categories defined neither in filter, nore in event list
                $allowAllCats = true;
            }
        }

        if(count($filterCats) > 0 || $allowAllCats) {
            if($allowAllCats) {
                $sqlSort = "SELECT * FROM  tl_mae_event_cat ORDER BY title";
            }
            else {
                $sqlSort = "SELECT * FROM  tl_mae_event_cat WHERE id IN (" . implode(",", $filterCats) . ") ORDER BY title";
            }
            $objCats = $this->Database->execute($sqlSort);
            while ($item = $objCats->fetchAssoc()) {
                if($selectedCat == $item['id']) {
                    $item['cssClass'] = $item['cssClass'] . " active";
                }
                if(!empty($item['cssClass'])) {
                    $item['cssClass'] = " " . trim($item['cssClass']);
                }
                if(empty($item['cssId'])) {
                    $item['cssId'] = "mae_cat_" . $item['id'];
                }
                $item['href'] = $this->addToUrl('category=' . $item['id']);
                $items[] = $item;
            }
        } // if have categories
        $this->Template->items = $items;
    }
}