<?php declare(strict_types=1);

namespace SchoolPlugin;

use Doctrine\DBAL\Connection;
use SchoolPlugin\Installer\FlowInstaller;
use SchoolPlugin\Installer\MailTemplateInstaller;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
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
        $schemaManager = $connection->createSchemaManager();

        $categoryIds = [];

        if ($schemaManager->tablesExist(['school'])) {
            $categoryIds = $connection->fetchFirstColumn(
                '
                SELECT LOWER(HEX(category_id))
                FROM school
                WHERE category_id IS NOT NULL
                '
            );
        }

        /** @var EntityRepository $categoryRepository */
        $categoryRepository = $this->container->get('category.repository');

        foreach ($categoryIds as $categoryId) {
            $this->deleteCategoryTree(
                $categoryId,
                $categoryRepository,
                $connection,
                $context->getContext()
            );
        }

        (new FlowInstaller($connection))->uninstall();
        (new MailTemplateInstaller($connection))->uninstall();

        $connection->executeStatement(
            'DROP TABLE IF EXISTS `school_product_price`'
        );

        $connection->executeStatement(
            'DROP TABLE IF EXISTS `school_parent_invitation`'
        );

        $connection->executeStatement(
            'DROP TABLE IF EXISTS `school`'
        );
    }

    private function deleteCategoryTree(
        string $categoryId,
        EntityRepository $categoryRepository,
        Connection $connection,
        Context $context
    ): void {
        $children = $connection->fetchFirstColumn(
            '
            SELECT LOWER(HEX(id))
            FROM category
            WHERE parent_id = UNHEX(?)
            ',
            [$categoryId]
        );

        foreach ($children as $childId) {
            $this->deleteCategoryTree(
                $childId,
                $categoryRepository,
                $connection,
                $context
            );
        }

        $categoryRepository->delete(
            [
                [
                    'id' => $categoryId,
                ],
            ],
            $context
        );
    }

    public function getActionEventClasses(): array
    {
        return [
            \SchoolPlugin\Event\SchoolApprovedEvent::class,
            \SchoolPlugin\Event\SchoolParentInvitedEvent::class,
        ];
    }
}