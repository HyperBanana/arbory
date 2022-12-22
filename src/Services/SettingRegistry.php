<?php

namespace Arbory\Base\Services;

use Arbory\Base\Admin\Form\Fields\Translatable;
use Arbory\Base\Admin\Settings\Setting;
use Arbory\Base\Admin\Settings\SettingDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SettingRegistry
{
    /**
     * @var Collection
     */
    protected Collection $settings;

    /**
     * SettingRegistry constructor.
     */
    public function __construct()
    {
        $this->settings = new Collection();
    }

    /**
     * @param SettingDefinition $definition
     * @return void
     */
    public function register(SettingDefinition $definition): void
    {
        $this->settings->put($definition->getKey(), $definition);
    }

    /**
     * @param string $key
     * @return SettingDefinition|null
     */
    public function find(string $key): ?SettingDefinition
    {
        return $this->settings->get($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function contains(string $key): bool
    {
        return $this->settings->has($key);
    }

    /**
     * @return Collection
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    /**
     * @return void
     */
    public function importFromDatabase(): void
    {
        Setting::with(config('arbory.settings.relations', ['translations', 'file']))
            ->get()
            ->each(function (Setting $setting) {
                $definition = $this->find($setting->name);

                if ($definition) {
                    $definition->setModel($setting);
                    $definition->setValue($setting->value);
                }
            });
    }

    /**
     * @param array $properties
     * @param string $before
     */
    public function importFromConfig(array $properties, string $before = ''): void
    {
        foreach ($properties as $key => $data) {
            if (is_array($data) && ! empty($data) && ! array_key_exists('value', $data)) {
                $this->importFromConfig($data, $before . $key . '.');
            } else {
                $key = $before . $key;
                $value = $data['value'] ?? $data;
                $type = $data['type'] ?? null;

                if ($type) {
                    $value = Arr::get($data, 'value');
                }

                if (is_array($value)) {
                    if ($type === Translatable::class) {
                        $value = Arr::get($value, 'value');
                        $value = Arr::get($value, request()->getLocale(), $value);
                    }
                }

                $definition = new SettingDefinition($key, $value, $type, $data);
                $this->register($definition);
            }
        }
    }
}
