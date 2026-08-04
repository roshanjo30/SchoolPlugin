<?php declare(strict_types=1);

namespace SchoolPlugin\Installer;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;

class MailTemplateInstaller
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function install(): void
    {
        $languages = $this->connection->fetchAllAssociative(
            "
            SELECT
                language.id,
                locale.code
            FROM language
            INNER JOIN locale
                ON locale.id = language.translation_code_id
            "
        );

        $this->installSchoolApprovedTemplate($languages);
        $this->installParentInvitationTemplate($languages);
    }

    /**
     * @param array<int, array<string, mixed>> $languages
     */
    private function installSchoolApprovedTemplate(array $languages): void
    {
        $exists = $this->connection->fetchOne(
            "
            SELECT id
            FROM mail_template_type
            WHERE technical_name = 'school_approved'
            "
        );

        if ($exists) {
            return;
        }

        $mailTemplateTypeId = Uuid::randomBytes();
        $mailTemplateId = Uuid::randomBytes();
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $this->connection->insert('mail_template_type', [
            'id' => $mailTemplateTypeId,
            'technical_name' => 'school_approved',
            'available_entities' => json_encode([
                'school' => 'school',
                'salesChannel' => 'sales_channel',
            ]),
            'created_at' => $now,
        ]);

        foreach ($languages as $language) {
            $name = $language['code'] === 'de-DE'
                ? 'Schule genehmigt'
                : 'School approved';

            $this->connection->insert('mail_template_type_translation', [
                'mail_template_type_id' => $mailTemplateTypeId,
                'language_id' => $language['id'],
                'name' => $name,
                'created_at' => $now,
            ]);
        }

        $this->connection->insert('mail_template', [
            'id' => $mailTemplateId,
            'mail_template_type_id' => $mailTemplateTypeId,
            'created_at' => $now,
        ]);

        foreach ($languages as $language) {
            $code = $language['code'];

            if ($code === 'de-DE') {
                $subject = 'Ihre Schule wurde genehmigt';

                $html = '
                    <p>Hallo {{ school.contactPerson }},</p>

                    <p>
                        Ihre Schule
                        <strong>{{ school.schoolName }}</strong>
                        wurde genehmigt.
                    </p>

                    <p>
                        Kategorie URL:
                        <a href="{{ school.categoryUrl }}">
                            {{ school.categoryUrl }}
                        </a>
                    </p>

                    <p>
                        Viele Grüße
                    </p>
                ';

                $plain = '
                    Hallo {{ school.contactPerson }},

                    Ihre Schule {{ school.schoolName }}
                    wurde genehmigt.

                    {{ school.categoryUrl }}
                ';
            } else {
                $subject = 'Your school has been approved';
                $html = '
                    <p>Hello {{ school.contactPerson }},</p>

                    <p>
                        Your school
                        <strong>{{ school.schoolName }}</strong>
                        has been approved.
                    </p>

                    <p>
                        Category URL:
                        <a href="{{ school.categoryUrl }}">
                            {{ school.categoryUrl }}
                        </a>
                    </p>

                    <p>
                        Regards
                    </p>
                ';

                $plain = '
                    Hello {{ school.contactPerson }},

                    Your school {{ school.schoolName }}
                    has been approved.

                    {{ school.categoryUrl }}
                ';
            }

            $this->connection->insert(
                'mail_template_translation',
                [
                    'mail_template_id' => $mailTemplateId,
                    'language_id' => $language['id'],
                    'sender_name' => '{{ salesChannel.name }}',
                    'subject' => $subject,
                    'content_html' => $html,
                    'content_plain' => $plain,
                    'created_at' => $now,
                ]
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $languages
     */
    private function installParentInvitationTemplate(array $languages): void
        {
            $exists = $this->connection->fetchOne(
                "
                SELECT id
                FROM mail_template_type
                WHERE technical_name = 'school_parent_invitation'
                "
            );

            if ($exists) {
                return;
            }

            $mailTemplateTypeId = Uuid::randomBytes();
            $mailTemplateId = Uuid::randomBytes();
            $now = (new \DateTime())->format('Y-m-d H:i:s');

            $this->connection->insert('mail_template_type', [
                'id' => $mailTemplateTypeId,
                'technical_name' => 'school_parent_invitation',
                'available_entities' => json_encode([
                    'school' => 'school',
                    'salesChannel' => 'sales_channel',
                ]),
                'created_at' => $now,
            ]);

            foreach ($languages as $language) {
                $name = $language['code'] === 'de-DE'
                    ? 'Elterneinladung'
                    : 'Parent invitation';

                $this->connection->insert('mail_template_type_translation', [
                    'mail_template_type_id' => $mailTemplateTypeId,
                    'language_id' => $language['id'],
                    'name' => $name,
                    'created_at' => $now,
                ]);
            }

            $this->connection->insert('mail_template', [
                'id' => $mailTemplateId,
                'mail_template_type_id' => $mailTemplateTypeId,
                'created_at' => $now,
            ]);

            foreach ($languages as $language) {

                if ($language['code'] === 'de-DE') {

                    $subject = 'Einladung für {{ parentName }} - {{ schoolName }}';

                    $html = '
                        <p>Hallo {{ parentName }},</p>

                        <p>
                            Sie wurden eingeladen, Produkte für
                            <strong>{{ schoolName }}</strong>
                            zu bestellen.
                        </p>

                        <p>
                            Bitte öffnen Sie folgenden Link:
                        </p>

                        <p>
                            <a href="{{ categoryUrl }}">
                                {{ categoryUrl }}
                            </a>
                        </p>

                        <p>Viele Grüße</p>
                    ';

                    $plain = '
                        Hallo {{ parentName }},

                        Sie wurden eingeladen, Produkte für {{ schoolName }} zu bestellen.

                        Link:
                        {{ categoryUrl }}

                        Viele Grüße
                        ';

                } else {

                    $subject = 'Invitation for {{ parentName }} - {{ schoolName }}';

                    $html = '
                        <p>Hello {{ parentName }},</p>

                        <p>
                            You have been invited to order products for
                            <strong>{{ schoolName }}</strong>.
                        </p>

                        <p>
                            Please use the following link:
                        </p>

                        <p>
                            <a href="{{ categoryUrl }}">
                                {{ categoryUrl }}
                            </a>
                        </p>

                        <p>Regards</p>
                    ';

                    $plain = '
                        Hello {{ parentName }},

                        You have been invited to order products for {{ schoolName }}.

                        Please use the following link:

                        {{ categoryUrl }}

                        Regards
                        ';
                }

                $this->connection->insert(
                    'mail_template_translation',
                    [
                        'mail_template_id' => $mailTemplateId,
                        'language_id' => $language['id'],
                        'sender_name' => '{{ salesChannel.name }}',
                        'subject' => $subject,
                        'content_html' => $html,
                        'content_plain' => $plain,
                        'created_at' => $now,
                    ]
                );
            }
    }

    public function uninstall(): void
    {
        $this->uninstallTemplateType('school_approved');
        $this->uninstallTemplateType('school_parent_invitation');
    }

    private function uninstallTemplateType(string $technicalName): void
    {
        $mailTemplateTypeId = $this->connection->fetchOne(
            "
            SELECT id
            FROM mail_template_type
            WHERE technical_name = :technicalName
            ",
            ['technicalName' => $technicalName]
        );

        if (!$mailTemplateTypeId) {
            return;
        }

        $mailTemplateIds = $this->connection->fetchFirstColumn(
            "
            SELECT id
            FROM mail_template
            WHERE mail_template_type_id = :typeId
            ",
            ['typeId' => $mailTemplateTypeId]
        );

        foreach ($mailTemplateIds as $mailTemplateId) {
            $this->connection->delete('mail_template_translation', ['mail_template_id' => $mailTemplateId]);
            $this->connection->delete('mail_template', ['id' => $mailTemplateId]);
        }

        $this->connection->delete('mail_template_type_translation', ['mail_template_type_id' => $mailTemplateTypeId]);
        $this->connection->delete('mail_template_type', ['id' => $mailTemplateTypeId]);
    }
}