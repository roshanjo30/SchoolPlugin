<?php declare(strict_types=1);

namespace SchoolPlugin\Installer;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;

class FlowInstaller
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function install(): void
    {
        $this->installSchoolApprovalFlow();
        $this->installParentInvitationFlow();
    }

    private function installSchoolApprovalFlow(): void
    {
        $exists = $this->connection->fetchOne(
            "
            SELECT id
            FROM flow
            WHERE name = 'School approval notification'
            "
        );

        if ($exists) {
            return;
        }

        $flowId = Uuid::randomBytes();
        $sequenceId = Uuid::randomBytes();

        $now = (new \DateTime())->format('Y-m-d H:i:s');

        /*
         * Get mail template
         */
        $mailTemplateId = $this->connection->fetchOne(
            "
            SELECT mt.id
            FROM mail_template mt
            INNER JOIN mail_template_type mtt
                ON mt.mail_template_type_id = mtt.id
            WHERE mtt.technical_name = 'school_approved'
            "
        );

        if (!$mailTemplateId) {
            throw new \RuntimeException(
                'Mail template school_approved was not found'
            );
        }

        /*
         * Create flow
         */
        $this->connection->insert(
            'flow',
            [
                'id' => $flowId,
                'name' => 'School approval notification',
                'event_name' => 'school.approved',
                'active' => 1,
                'priority' => 1,
                'created_at' => $now,
            ]
        );

        /*
         * Mail action
         */
        $this->connection->insert(
            'flow_sequence',
            [
                'id' => $sequenceId,
                'flow_id' => $flowId,
                'parent_id' => null,
                'action_name' => 'action.mail.send',
                'config' => json_encode(
                    [
                        'mailTemplateId' => Uuid::fromBytesToHex($mailTemplateId),
                        'recipient' => [
                            'type' => 'default',
                            'data' => [],
                        ],
                    ],
                    JSON_THROW_ON_ERROR
                ),
                'position' => 1,
                'display_group' => 1,
                'true_case' => 0,
                'created_at' => $now,
            ]
        );
    }

    private function installParentInvitationFlow(): void
    {
        $exists = $this->connection->fetchOne(
            "
            SELECT id
            FROM flow
            WHERE name = 'School parent invitation'
            "
        );

        if ($exists) {
            return;
        }

        $flowId = Uuid::randomBytes();
        $sequenceId = Uuid::randomBytes();

        $now = (new \DateTime())->format('Y-m-d H:i:s');

        /*
         * Get mail template
         */
        $mailTemplateId = $this->connection->fetchOne(
            "
            SELECT mt.id
            FROM mail_template mt
            INNER JOIN mail_template_type mtt
                ON mt.mail_template_type_id = mtt.id
            WHERE mtt.technical_name = 'school_parent_invitation'
            "
        );

        if (!$mailTemplateId) {
            throw new \RuntimeException(
                'Mail template school_parent_invitation was not found'
            );
        }

        /*
         * Create flow
         */
        $this->connection->insert(
            'flow',
            [
                'id' => $flowId,
                'name' => 'School parent invitation',
                'event_name' => 'school.parent.invitation',
                'active' => 1,
                'priority' => 1,
                'created_at' => $now,
            ]
        );

        /*
         * Mail action
         */
        $this->connection->insert(
            'flow_sequence',
            [
                'id' => $sequenceId,
                'flow_id' => $flowId,
                'parent_id' => null,
                'action_name' => 'action.mail.send',
                'config' => json_encode(
                    [
                        'mailTemplateId' => Uuid::fromBytesToHex($mailTemplateId),
                        'recipient' => [
                            'type' => 'default',
                            'data' => [],
                        ],
                    ],
                    JSON_THROW_ON_ERROR
                ),
                'position' => 1,
                'display_group' => 1,
                'true_case' => 0,
                'created_at' => $now,
            ]
        );
    }

    public function uninstall(): void
    {
        $this->uninstallFlow('School approval notification');
        $this->uninstallFlow('School parent invitation');
    }

    private function uninstallFlow(string $flowName): void
    {
        $flowId = $this->connection->fetchOne(
            "
            SELECT id
            FROM flow
            WHERE name = :name
            ",
            [
                'name' => $flowName,
            ]
        );

        if (!$flowId) {
            return;
        }

        /*
         * Delete sequences first
         */
        $this->connection->delete(
            'flow_sequence',
            [
                'flow_id' => $flowId,
            ]
        );

        /*
         * Delete flow
         */
        $this->connection->delete(
            'flow',
            [
                'id' => $flowId,
            ]
        );
    }
}