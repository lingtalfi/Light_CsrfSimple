<?php


namespace Ling\Light_CsrfSimple\Service;


use Ling\Light\Events\LightEvent;
use Ling\Light\Tool\LightTool;


/**
 * The LightCsrfSimpleService class.
 */
class LightCsrfSimpleService
{

    /**
     * This property holds the sessionName for this instance.
     * You probably should never change it.
     *
     * @var string=light_csrf_simple
     */
    private $sessionName;


    /**
     * Builds the LightCsrfSimpleService instance.
     */
    public function __construct()
    {
        $this->sessionName = "light_csrf_simple";
    }


    /**
     * This is a callable to execute when the **Light.on_route_found** event is fired.
     * See the @page(events page) for more details.
     *
     * It calls the regenerate method if the page is a non-ajax page.
     *
     * @param LightEvent $event
     * @param string $eventName
     * @throws \Exception
     */
    public function onRouteFound(LightEvent $event, string $eventName)
    {
        if (false === LightTool::isAjax($event->getContainer())) {
            $this->regenerate();
        }
    }


    /**
     * Returns the csrf token value stored in the new slot.
     *
     *
     * @return string
     */
    public function getToken(): string
    {
        $this->startSession();
        return $_SESSION[$this->sessionName]['new'];
    }


    /**
     * Regenerates a new token, and moves the replaced token to the old slot.
     * This method should be called only on non-ajax pages.
     *
     */
    public function regenerate()
    {
        $this->startSession();
        $this->rotate();
    }


    /**
     * Returns whether the given token is valid.
     * The comparison is executed against the csrf token stored in the new slot by default.
     * To compare the token against the csrf token stored in the old slot, set the useOldSlot flag to true.
     *
     * @param string $token
     * @param bool $useOldSlot
     * @return bool
     */
    public function isValid(string $token, bool $useOldSlot = false): bool
    {
        $this->startSession();
        if (false === $useOldSlot) {
            return $token === $_SESSION[$this->sessionName]['new'];
        }
        return $token === $_SESSION[$this->sessionName]['old'];
    }




    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Ensures that the php session has started.
     */
    protected function startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (false === array_key_exists($this->sessionName, $_SESSION)) {
            $_SESSION[$this->sessionName] = [
                'old' => '',
                'new' => '',
            ];
            $this->rotate();
        }
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * Regenerates a new token, and moves the replaced token to the old slot.
     */
    private function rotate()
    {
        $_SESSION[$this->sessionName]['old'] = $_SESSION[$this->sessionName]['new'];
        $newToken = md5(uniqid()); // is this enough?
        $_SESSION[$this->sessionName]['new'] = $newToken;
    }

}