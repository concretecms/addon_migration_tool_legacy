<?php

class UserExportType extends AbstractExportType
{
    public function getHeaders()
    {
        return array(
            t('Name'),
            t('Email'),
        );
    }

    public function getResultColumns(MigrationBatchItem $exportItem)
    {
        $ui = UserInfo::getByID($exportItem->getItemIdentifier());

        return array(
            $ui->getUserName(),
            $ui->getUserEmail(),
        );
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $ui = UserInfo::getByID($id);
            if ($ui) {
                $user = new MigrationBatchItem();
                $user->setType('user');
                $user->setItemId($ui->getUserID());
                $items[] = $user;
            }
        }

        return $items;
    }

    protected function getSetUserAttributes(UserInfo $user)
    {
        $db = Loader::db();
        $akIDs = $db->GetCol("select akID from UserAttributeValues where uID = ?", array($user->getUserID()));
        $attribs = array();
        foreach($akIDs as $akID) {
            $attribs[] = UserAttributeKey::getByID($akID);
        }
        return $attribs;

    }

    public function exportCollection($collection, \SimpleXMLElement $element)
    {
        $element = $element->addChild('users');
        foreach ($collection->getItems() as $object) {
            $user = UserInfo::getByID($object->getItemIdentifier());
            if ($user) {
                $node = $element->addChild('user');

                // basic information
                $node->addAttribute('username', $user->getUserName());
                $node->addAttribute('email', $user->getUserEmail());
                if (!$user->isActive()) {
                    $node->addAttribute('inactive', 1);
                }
                if (!$user->isValidated()) {
                    $node->addAttribute('unvalidated', 1);
                }
                if ($timezone = $user->getUserObject()->getUserTimezone()) {
                    $node->addAttribute('timezone', $timezone);
                }

                if ($language = $user->getUserObject()->getUserDefaultLanguage()) {
                    $node->addAttribute('language', $language);
                }

                // attributes
                $attribs = $this->getSetUserAttributes($user);
                if (count($attribs) > 0) {
                    $attributes = $node->addChild('attributes');
                    foreach($attribs as $ak) {
                        $av = $user->getAttributeValueObject($ak);
                        $cnt = $ak->getController();
                        $cnt->setAttributeValue($av);
                        $akx = $attributes->addChild('attributekey');
                        $akx->addAttribute('handle', $ak->getAttributeKeyHandle());
                        $cnt->exportValue($akx);
                    }
                }


                // groups
                $groups = $user->getUserObject()->getUserGroups();

                if (count($groups)) {
                    $child = $node->addChild('groups');
                    foreach($groups as $key => $value) {
                        if ($key > REGISTERED_GROUP_ID) {
                            $group = $child->addChild('group');
                            $group->addAttribute('name', $value);
                        }
                    }
                }


                unset($user);
                unset($category);
            }
        }
        return $element;
    }

    public function getResults($query)
    {
        $list = new UserList();
        $list->sortBy('uName', 'asc');

        $keywords = $query['keywords'];
        $gID = $query['gID'];
        $datetime = Loader::helper('form/date_time')->translate('datetime', $query);

        if ($datetime) {
            $list->filterByDateAdded($datetime, '>=');
        }

        if ($gID) {
            $group = Group::getByID($gID);
            $list->filterByGroup($group->getGroupName());
        }
        if ($keywords) {
            $list->filterByKeywords($keywords);
        }
        $list->setItemsPerPage(1000);
        $results = $list->getPage();
        $items = array();
        foreach ($results as $user) {
            $item = new MigrationBatchItem();
            $item->setType('user');
            $item->setItemId($user->getUserID());
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'user';
    }

    public function getPluralDisplayName()
    {
        return t('Users');
    }
}
