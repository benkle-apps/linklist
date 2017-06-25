<?php
/*
 * Copyright (c) 2017 Benjamin Kleiner
 *
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


namespace AppBundle\Listener;


use AppBundle\Entity\Domain;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Routing\Router;

class SerializationListener implements EventSubscriberInterface
{
    /** @var  Router */
    private $router;

    /**
     * SerializationListener constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Returns the events to which this class has subscribed.
     *
     * Return format:
     *     array(
     *         array('event' => 'the-event-name', 'method' => 'onEventName', 'class' => 'some-class', 'format' => 'json'),
     *         array(...),
     *     )
     *
     * The class may be omitted if the class wants to subscribe to events of all classes.
     * Same goes for the format key.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ['event' => Events::POST_SERIALIZE, 'class' => Domain::class, 'method' => 'serializeDomain'],
        ];
    }

    public function serializeDomain(ObjectEvent $event)
    {
        /** @var Domain $domain */
        $domain = $event->getObject();
        $event->getVisitor()->addData(
            'url',
            $this->getRouter()->generate(
                'app_list_urls_by_domain', [
                'domain' => $domain->getName(),
                'page'   => 1,
            ]
            )
        );
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }
}
