<?php declare(strict_types=1);

namespace SchoolPlugin;

use Doctrine\DBAL\Connection;
use SchoolPlugin\Installer\FlowInstaller;
use SchoolPlugin\Installer\MailTemplateInstaller;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class SchoolPlugin extends Plugin
{
    public function install(InstallContext $context): void
    {
        parent::install($context);

        $connection = $this->container->get(Connection::class);

        (new MailTemplateInstaller($connection))->install();
        (new FlowInstaller($connection))->install();
    }

    

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);

        $categoryIds = $connection->fetchFirstColumn(
            'SELECT LOWER(HEX(category_id))
             FROM school
             WHERE category_id IS NOT NULL'
        );
        
        $categoryRepository = $this->container->get('category.repository');
        
        foreach ($categoryIds as $categoryId) {
        
            $children = $connection->fetchFirstColumn(
                'SELECT LOWER(HEX(id))
                 FROM category
                 WHERE parent_id = UNHEX(?)',
                [$categoryId]
            );
        
            $delete = [];
        
            foreach ($children as $childId) {
                $delete[] = ['id' => $childId];
            }
        
            $delete[] = ['id' => $categoryId];
        
            $categoryRepository->delete(
                $delete,
                Context::createDefaultContext()
            );
        }

        (new FlowInstaller($connection))->uninstall();
        (new MailTemplateInstaller($connection))->uninstall();

          // Drop plugin tables
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        $connection->executeStatement('DROP TABLE IF EXISTS `school_product_price`');
        $connection->executeStatement('DROP TABLE IF EXISTS `school_parent_invitation`');
        $connection->executeStatement('DROP TABLE IF EXISTS `school`');

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}