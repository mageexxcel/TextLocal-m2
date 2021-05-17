<?php
namespace Excellence\Sms\Api;

use Excellence\Sms\Model\TemplateInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface TemplateRepositoryInterface 
{
    public function save(TemplateInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(TemplateInterface $page);

    public function deleteById($id);
}
