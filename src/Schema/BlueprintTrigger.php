<?php

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Fluent;

trait BlueprintTrigger
{
    /**
     * Create a new trigger on the table.
     */
    public function trigger(string $name, string $action, string $fire): TriggerDefinition
    {
        $trigger = new TriggerDefinition(['name' => 'trigger', 'trigger' => $name, ...compact('action', 'fire')]);
        $this->commands[] = $trigger;

        return $trigger;
    }

    /**
     * Indicate that the given trigger should be dropped.
     */
    public function dropTrigger(string $name): Fluent
    {
        return $this->addCommand('dropTrigger', ['trigger' =>  $name]);
    }

    /**
     * Indicate that the given trigger should be dropped if it exists.
     */
    public function dropTriggerIfExists(string $name): Fluent
    {
        return $this->addCommand('dropTriggerIfExists', ['trigger' =>  $name]);
    }
}
