<?php

namespace AmoCrm;

class WebhookHandler
{
    public function handle(array $data): ?array
    {
        file_put_contents(__DIR__ . '/../../logs/app.log', print_r($data, true), FILE_APPEND);

        if (isset($data['leads']['add'])) {
            return $this->handleLeadAdd($data['leads']['add'][0]);
        } elseif (isset($data['contacts']['add'])) {
            return $this->handleContactAdd($data['contacts']['add'][0]);
        } elseif (isset($data['leads']['update'])) {
            return $this->handleLeadUpdate($data['leads']['update'][0]);
        } elseif (isset($data['contacts']['update'])) {
            return $this->handleContactUpdate($data['contacts']['update'][0]);
        }
        return null;
    }

    private function handleLeadAdd(array $lead): array
    {
        $text = sprintf(
            "Создана сделка: %s\nОтветственный: %s\nВремя: %s",
            $lead['name'] ?? '-',
            $lead['responsible_user_id'] ?? '-',
            date('Y-m-d H:i:s', $lead['date_create'] ?? time())
        );
        return [
            'entity_id' => $lead['id'],
            'entity_type' => 'leads',
            'note' => $text
        ];
    }

    private function handleContactAdd(array $contact): array
    {
        $text = sprintf(
            "Создан контакт: %s\nОтветственный: %s\nВремя: %s",
            $contact['name'] ?? '-',
            $contact['responsible_user_id'] ?? '-',
            date('Y-m-d H:i:s', $contact['date_create'] ?? time())
        );
        return [
            'entity_id' => $contact['id'],
            'entity_type' => 'contacts',
            'note' => $text
        ];
    }

    private function handleLeadUpdate(array $lead): array
    {
        $changedFields = $this->getChangedFields($lead);
        $text = sprintf(
            "Изменена сделка: %s\nИзменённые поля: %s\nВремя: %s",
            $lead['name'] ?? '-',
            $changedFields,
            date('Y-m-d H:i:s', $lead['last_modified'] ?? time())
        );
        return [
            'entity_id' => $lead['id'],
            'entity_type' => 'leads',
            'note' => $text
        ];
    }

    private function handleContactUpdate(array $contact): array
    {
        $changedFields = $this->getChangedFields($contact);
        $text = sprintf(
            "Изменён контакт: %s\nИзменённые поля: %s\nВремя: %s",
            $contact['name'] ?? '-',
            $changedFields,
            date('Y-m-d H:i:s', $contact['last_modified'] ?? time())
        );
        return [
            'entity_id' => $contact['id'],
            'entity_type' => 'contacts',
            'note' => $text
        ];
    }

    private function getChangedFields(array $entity): string
    {
        $fields = [];

        $standardFields = [
            'name' => 'Имя',
            'company_name' => 'Компания',
            'status_id' => 'Статус',
            'price' => 'Бюджет',
        ];

        foreach ($standardFields as $key => $label) {
            if (isset($entity[$key]) && $entity[$key] !== '') {
                $fields[] = $label . ': ' . $entity[$key];
            }
        }

        if (isset($entity['custom_fields'])) {
            foreach ($entity['custom_fields'] as $field) {
                $value = isset($field['values'][0]['value']) ? $field['values'][0]['value'] : '-';
                $fields[] = $field['name'] . ': ' . $value;
            }
        }

        return $fields ? implode(', ', $fields) : '-';
    }
} 