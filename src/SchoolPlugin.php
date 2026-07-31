<?php declare(strict_types=1);

namespace SchoolPlugin;

use Doctrine\DBAL\Connection;
use SchoolPlugin\Installer\FlowInstaller;
use SchoolPlugin\Installer\MailTemplateInstaller;
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

        (new FlowInstaller($connection))->uninstall();
        (new MailTemplateInstaller($connection))->uninstall();
    }
}