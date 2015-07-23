<?php
namespace Concrete\Package\AttributeForms\Controller\SinglePage\Dashboard\Forms;

use Concrete\Package\AttributeForms\Src\Model\AttributeForm,
    Concrete\Package\AttributeForms\Src\Model\AttributeFormList,
    Concrete\Package\AttributeForms\Src\Model\AttributeFormTypeList,
    PageController,
    Loader,
    Page,
    Concrete\Package\AttributeForms\Src\Model\AttributeFormType;

class Results extends PageController
{
    protected $helpers = array('form', 'date');

    public function view()
    {
        $currentPage = Page::getCurrentPage();
        $aftl = new AttributeFormTypeList();
        $aftl->sortByFormName();
        $this->set('formTypes', $aftl->getPage());
        $this->set('formTypesPagination', $aftl->displayPagingV2(Loader::helper('navigation')->getLinkToCollection($currentPage), true));
    }

    public function entries($aftID)
    {
        $currentPage = Page::getCurrentPage();
        $aft = AttributeFormType::getByID($aftID);
        $afl = new AttributeFormList();
        $afl->filterByType($aft);
        $afl->sortByDateCreated('desc');
        $this->set('showSpam', !$aft->getDeleteSpam());
        $this->set('formName', $aft->getFormName());
        $this->set('forms', $afl->getPage());
        $this->set('formsPagination', $afl->displayPagingV2(Loader::helper('navigation')->getLinkToCollection($currentPage), true));
    }

    public function excel($aftID)
    {
        header("Content-Type: application/vnd.ms-excel");
        header("Cache-control: private");
        header("Pragma: public");
        header("Content-Disposition: inline; filename=form_entries_{$aftID}.xls");
        header("Content-Title: Form Entries {$aftID}");

        $aft = AttributeFormType::getByID($aftID);
        $afl = new AttributeFormList();
        $afl->sortByDateCreated('desc');
        $afl->filterByType($aft);

        $attributes = $aft->getAttributeObjects();

        echo '<table>';

        // Add table header
        $headers = [t('ID'), t('Date Created')];

        foreach ($attributes as $attribute) {
            $headers[] = $attribute->getAttributeKeyDisplayName();
        }
        $entries = $afl->get(0);

        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th>' . $header . '</th>';
        }
        echo '</tr>';

        // Add table content
        foreach ($entries as $entry) {
            echo '<tr>';
            echo '<td>' . $entry->getID() . '</td>';
            echo '<td>' . $entry->getDateCreated() . '</td>';

            foreach ($attributes as $attribute) {
                echo '<td>';
                echo $entry->getAttribute($attribute, 'display');
                echo '</td>';

            }

            echo '</tr>';
        }
        echo '</table>';
        die();

    }

    public function detail($afID)
    {
        $af = AttributeForm::getByID($afID);
        $aft = AttributeFormType::getByID($af->getTypeID());
        $attributes = $aft->getAttributeObjects();

        $this->set('af', $af);
        $this->set('afID', $afID);
        $this->set('attributes', $attributes);
    }
}
