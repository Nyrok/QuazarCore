<?php

namespace Nyrok\QuazarCore\managers;



abstract class EventsManager
{
    /**
     * @var Events[]
     */
    public static array $events = [];
    
    /**
     * @return array
     */
    public static function getEvents(): array
    {
        return self::$events;
    }
    
    /**
     * @param string $name
     * @return Event
     */
    public static function getEvent(string $name): Event
    {
        return self::$events[$name];
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function addEvent(Event $event): void
    {
        self::$events[$event->getHost()->getName()] = $event;
        Server::getInstance()->broadcastMessage();
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function removeEvent(Event $event): void
    {
        unset(self::$events[$event->getHost()->getName()]);
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function startEvent(Event $event): void
    {
        
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function endEvent(Event $event): void
    {
        
    }
}