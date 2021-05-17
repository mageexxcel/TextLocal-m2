<?php
namespace Excellence\Sms\Api;

use Excellence\Sms\Model\SmsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SmsRepositoryInterface 
{
    public function save(SmsInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(SmsInterface $page);

    public function deleteById($id);
}
