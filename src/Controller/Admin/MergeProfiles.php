<?php

namespace Iidev\MergeProfiles\Controller\Admin;

use XLite\Core\Database;
use XLite\Core\Request;

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
            ->select('m.profile_id', 'm.login', 'COUNT(o.order_id) as orders_count')
            ->leftJoin('XLite\Model\Order', 'o', 'WITH', 'm.profile_id = o.orig_profile AND o.not_finished_order IS NULL')
            ->where($queryBuilder->expr()->like('m.login', ':search'))
            ->andWhere('m.order IS NULL')
            ->setParameter('search', $query . '%')
            ->groupBy('m.profile_id', 'm.login')
            ->setMaxResults(5);

        $data = $queryBuilder->getQuery()->getResult();

        $this->displayJSON($data);
        $this->setSuppressOutput(true);
        $this->set('silent', true);
    }

    // public function getActiveSubscribersCount()
    // {
    //     $subscriptions = Database::getRepo('Iidev\StripeSubscriptions\Model\MembershipMigrate')->findBy([
    //         'membershipid' => 9
    //     ]);
    //     return count($subscriptions);
    // }
    // public function getMigratedSubscribersCount()
    // {
    //     $queryBuilder = Database::getRepo('Iidev\StripeSubscriptions\Model\MembershipMigrate')->createQueryBuilder('m');
    //     $queryBuilder->where('m.membershipid = :membershipid')
    //         ->andWhere('m.status = :status')
    //         ->setParameter('membershipid', 9)
    //         ->setParameter('status', 'MIGRATION_COMPLETE');

    //     $subscriptions = $queryBuilder->getQuery()->getResult();
    //     return count($subscriptions);
    // }
}

