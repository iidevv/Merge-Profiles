<?php

namespace Iidev\MergeProfiles\Controller\Admin;

use XLite\Core\Database;
use XLite\Core\Request;
use \XLite\Core\TopMessage;

class MergeProfiles extends \XLite\Controller\Admin\AAdmin
{
    public function checkAccess()
    {
        return true;
    }

    protected function doActionSearchProfiles()
    {
        $query = Request::getInstance()->query;

        $queryBuilder = Database::getRepo('XLite\Model\Profile')->createQueryBuilder('m');

        $queryBuilder
            ->select('m.profile_id', 'm.login', 'COUNT(DISTINCT o.order_id) as orders_count', "GROUP_CONCAT(DISTINCT CONCAT('{\"', o.order_id, '\": ', o.orderNumber, '}') SEPARATOR ', ') as orders")
            ->leftJoin('XLite\Model\Order', 'o', 'WITH', 'm.profile_id = o.orig_profile AND o.not_finished_order IS NULL')
            ->where($queryBuilder->expr()->like('m.login', ':search'))
            ->orWhere($queryBuilder->expr()->like('o.orderNumber', ':search'))
            ->andWhere('m.order IS NULL')
            ->setParameter('search', $query . '%')
            ->groupBy('m.profile_id', 'm.login')
            ->setMaxResults(5);

        $data = $queryBuilder->getQuery()->getResult();

        foreach ($data as &$result) {
            if (!empty($result['orders'])) {
                $jsonOrdersString = '[' . $result['orders'] . ']';
                $result['orders'] = json_decode($jsonOrdersString, true);
            } else {
                $result['orders'] = [];
            }
        }

        $this->displayJSON($data);
        $this->setSuppressOutput(true);
        $this->set('silent', true);
    }

    protected function doActionMergeProfiles()
    {
        $query = Request::getInstance()->getPostData();

        $orders = (array) $query['orders_' . $query['results-1']];

        $profile = Database::getRepo('XLite\Model\Profile')->find($query['results-2']);

        if (empty($query['results-1']) || empty($query['results-2'])) {
            TopMessage::addError('No profiles selected.');
            return;
        }

        if (!$profile) {
            TopMessage::addError('The profile ID provided does not correspond to a valid profile.');
            return;
        }

        if (empty($orders)) {
            TopMessage::addError('No orders selected.');
            return;
        }

        if ($query['results-1'] === $query['results-2']) {
            TopMessage::addError('Error! The same profiles have been selected.');
            return;
        }

        try {
            foreach ($orders as $orderId) {
                $order = Database::getRepo('XLite\Model\Order')->find($orderId);
                if ($order) {
                    $this->registerOrderProfileChange($order);

                    $order->setProfile($profile);
                    $order->setOrigProfile($profile);


                    Database::getEM()->persist($order);
                } else {
                    TopMessage::addError("Order ID $orderId not found.");
                }
            }

            Database::getEM()->flush();

            TopMessage::addInfo('Orders updated successfully.');

        } catch (\Exception $e) {
            Database::getEM()->rollback();
            TopMessage::addError('Error! No orders were updated. Check if the order IDs are correct: ' . $e->getMessage());
        }


    }

    public function registerOrderProfileChange($order)
    {
        $profile = $order->getProfile();
        \XLite\Core\OrderHistory::getInstance()->registerEvent(
            $order->getOrderId(),
            'ORDER MERGED',
            'Order profile changed',
            [],
            '',
            [
                [
                    'name' => (string) 'Previous order profile',
                    'value' => (string) static::t($profile->getLogin()),
                ],
            ]
        );
    }
}

