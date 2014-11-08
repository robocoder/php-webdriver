<?php
/**
 * Copyright 2004-2014 Facebook. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package WebDriver
 *
 * @author Justin Bishop <jubishop@gmail.com>
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */

namespace WebDriver;

/**
 * WebDriver\Session class
 *
 * @package WebDriver
 *
 * @method string window_handle() Retrieve the current window handle.
 * @method array window_handles() Retrieve the list of all window handles available to the session.
 * @method string url() Retrieve the URL of the current page
 * @method void postUrl($jsonUrl) Navigate to a new URL
 * @method void forward() Navigates forward in the browser history, if possible.
 * @method void back() Navigates backward in the browser history, if possible.
 * @method void refresh() Refresh the current page.
 * @method mixed execute($jsonScript) Inject a snippet of JavaScript into the page for execution in the context of the currently selected frame. (synchronous)
 * @method mixed execute_async($jsonScript) Inject a snippet of JavaScript into the page for execution in the context of the currently selected frame. (asynchronous)
 * @method string screenshot() Take a screenshot of the current page.
 * @method array getCookie() Retrieve all cookies visible to the current page.
 * @method array postCookie($jsonCookie) Set a cookie.
 * @method string source() Get the current page source.
 * @method string title() Get the current page title.
 * @method void keys($jsonKeys) Send a sequence of key strokes to the active element.
 * @method string getOrientation() Get the current browser orientation.
 * @method void postOrientation($jsonOrientation) Set the current browser orientation.
 * @method string getAlert_text() Gets the text of the currently displayed JavaScript alert(), confirm(), or prompt() dialog.
 * @method void postAlert_text($jsonText) Sends keystrokes to a JavaScript prompt() dialog.
 * @method void accept_alert() Accepts the currently displayed alert dialog.
 * @method void dismiss_alert() Dismisses the currently displayed alert dialog.
 * @method void moveto($jsonCoordinates) Move the mouse by an offset of the specified element (or current mouse cursor).
 * @method void click($jsonButton) Click any mouse button (at the coordinates set by the last moveto command).
 * @method void buttondown() Click and hold the left mouse button (at the coordinates set by the last moveto command).
 * @method void buttonup() Releases the mouse button previously held (where the mouse is currently at).
 * @method void doubleclick() Double-clicks at the current mouse coordinates (set by moveto).
 * @method array execute_sql($jsonQuery) Execute SQL.
 * @method array getLocation() Get the current geo location.
 * @method void postLocation($jsonCoordinates) Set the current geo location.
 * @method boolean getBrowser_connection() Is browser online?
 * @method void postBrowser_connection($jsonState) Set browser online.
 */
class Session extends Container
{
    /**
     * @var array
     */
    private $capabilities = null;

    /**
     * {@inheritdoc}
     */
    protected function methods()
    {
        return array(
            'window_handle'      => array('GET'),         // WD:getCurrentWindowHandle
            'window_handles'     => array('GET'),         // WD:getWindowHandles
            'url'                => array('GET', 'POST'), // WD:getCurrentUrl; WD:get - alternate for POST, use open($url)
            'forward'            => array('POST'),        // WD:goForward
            'back'               => array('POST'),        // WD:goBack
            'refresh'            => array('POST'),        // WD:refresh
            'execute'            => array('POST'),        // WD:executeScript
            'execute_async'      => array('POST'),        // WD:executeAsyncScript
            'screenshot'         => array('GET'),         // WD:screenshot
            'cookie'             => array('GET', 'POST'), // for DELETE, use deleteAllCookies()
            'source'             => array('GET'),         // WD:getPageSource
            'title'              => array('GET'),         // WD:getTitle
            'keys'               => array('POST'),        // WD:sendKeysToActiveElement
            'orientation'        => array('GET', 'POST'), // WD:getScreenOrientation, WD:setScreenOrientation
            'alert_text'         => array('GET', 'POST'), // WD:getAlertText, WD:setAlertValue
            'accept_alert'       => array('POST'),        // WD:acceptAlert
            'dismiss_alert'      => array('POST'),        // WD:dismissAlert
            'moveto'             => array('POST'),        // WD:mouseMoveTo
            'click'              => array('POST'),        // WD:mouseClick
            'buttondown'         => 'POST',               // WD:mouseButtonDown
            'buttonup'           => array('POST'),        // WD:mouseButtonUp
            'doubleclick'        => array('POST'),        // WD:mouseDoubleClick
            'location'           => array('GET', 'POST'), // WD:getLocation, WD:setLocation
            'browser_connection' => array('GET', 'POST'), // WD:isBrowserOnline, WD:setBrowserOnLine

            // specific to Java SeleniumServer
            'file'               => array('POST'),        // WD:uploadFile
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function obsoleteMethods()
    {
        return array(
            'modifier'    => array('POST'),
            'speed'       => array('GET', 'POST'),
            'alert'       => array('GET'),
            'visible'     => array('GET', 'POST'),
            'execute_sql' => array('POST'),
        );
    }

    /**
     * Open URL: /session/:sessionId/url (POST)
     * An alternative to $session->url($url);
     *
     * @internal WD:get
     *
     * @param string $url
     *
     * @return \WebDriver\Session
     */
    public function open($url)
    {
        $this->curl('POST', '/url', array('url' => $url));

        return $this;
    }

    /**
     * Get browser capabilities: /session/:sessionId (GET)
     *
     * @internal WD:getCapabilities
     *
     * @return mixed
     */
    public function capabilities()
    {
        if ( ! isset($this->capabilities)) {
            $result = $this->curl('GET', '');

            $this->capabilities = $result['value'];
        }

        return $this->capabilities;
    }

    /**
     * Close session: /session/:sessionId (DELETE)
     *
     * @internal WD:quit
     *
     * @return mixed
     */
    public function close()
    {
        $result = $this->curl('DELETE', '');

        return $result['value'];
    }

    // There's a limit to our ability to exploit the dynamic nature of PHP when it
    // comes to the cookie methods because GET and DELETE request methods are indistinguishable
    // from each other: neither takes parameters.

    /**
     * Get all cookies: /session/:sessionId/cookie (GET)
     * Alternative to: $session->cookie();
     *
     * Note: get cookie by name not implemented in API
     *
     * @internal WD:getCookies
     *
     * @return mixed
     */
    public function getAllCookies()
    {
        $result = $this->curl('GET', '/cookie');

        return $result['value'];
    }

    /**
     * Set cookie: /session/:sessionId/cookie (POST)
     * Alternative to: $session->cookie($cookie_json);
     *
     * @internal WD:addCookie
     *
     * @param array $cookieJson
     *
     * @return \WebDriver\Session
     */
    public function setCookie($cookieJson)
    {
        $this->curl('POST', '/cookie', array('cookie' => $cookieJson));

        return $this;
    }

    /**
     * Delete all cookies: /session/:sessionId/cookie (DELETE)
     *
     * @internal WD:deleteAllCookies
     *
     * @return \WebDriver\Session
     */
    public function deleteAllCookies()
    {
        $this->curl('DELETE', '/cookie');

        return $this;
    }

    /**
     * Delete a cookie: /session/:sessionId/cookie/:name (DELETE)
     *
     * @internal WD:deleteCookie
     *
     * @param string $cookieName
     *
     * @return \WebDriver\Session
     */
    public function deleteCookie($cookieName)
    {
        $this->curl('DELETE', '/cookie/' . $cookieName);

        return $this;
    }

    /**
     * window methods: /session/:sessionId/window (POST, DELETE)
     * - $session->window() - close current window
     * - $session->window($name) - set focus
     * - $session->window($window_handle)->method() - chaining
     *
     * @internal WD:close, WD:switchToWindow
     *
     * @return \WebDriver\Window|\WebDriver\Session
     */
    public function window()
    {
        // close current window
        if (func_num_args() === 0) {
            $this->curl('DELETE', '/window');

            return $this;
        }

        // set focus
        $arg = func_get_arg(0); // window handle or name attribute

        if (is_array($arg)) {
            $this->curl('POST', '/window', $arg);

            return $this;
        }

        // chaining
        return new Window($this->url . '/window', $arg);
    }

    /**
     * Delete window: /session/:sessionId/window (DELETE)
     *
     * @return \WebDriver\Session
     */
    public function deleteWindow()
    {
        $this->curl('DELETE', '/window');

        return $this;
    }

    /**
     * Set focus to window: /session/:sessionId/window (POST)
     *
     * @param mixed $name window handler or name attribute
     *
     * @return \WebDriver\Session
     */
    public function focusWindow($name)
    {
        $this->curl('POST', '/window', array('name' => $name));

        return $this;
    }

    /**
     * frame methods: /session/:sessionId/frame (POST)
     * - $session->frame($json) - change focus to another frame on the page
     * - $session->frame()->method() - chaining
     *
     * @internal WD:switchToFrame
     *
     * @return \WebDriver\Session|\WebDriver\Frame
     */
    public function frame()
    {
        if (func_num_args() === 1) {
            $arg = func_get_arg(0); // json

            $this->curl('POST', '/frame', $arg);

            return $this;
        }

        // chaining
        return new Frame($this->url . '/frame');
    }

    /**
     * timeouts methods: /session/:sessionId/timeouts (POST)
     * - $session->timeouts($json) - set timeout for an operation
     * - $session->timeouts()->method() - chaining
     *
     * @internal WD:setTimeout
     *
     * @return \WebDriver\Session|\WebDriver\Timeouts
     */
    public function timeouts()
    {
        // set timeouts
        if (func_num_args() === 1) {
            $arg = func_get_arg(0); // json

            $this->curl('POST', '/timeouts', $arg);

            return $this;
        }

        if (func_num_args() === 2) {
            $arg = array(
                'type' => func_get_arg(0), // 'script' or 'implicit'
                'ms' => func_get_arg(1),   // timeout in milliseconds
            );

            $this->curl('POST', '/timeouts', $arg);

            return $this;
        }

        // chaining
        return new Timeouts($this->url . '/timeouts');
    }

    /**
     * ime method chaining, e.g.,
     * - $session->ime()->method()
     *
     * @return \WebDriver\Ime
     */
    public function ime()
    {
        return new Ime($this->url . '/ime');
    }

    /**
     * Get active element (i.e., has focus): /session/:sessionId/element/active (POST)
     * - $session->activeElement()
     *
     * @internal WD:getActiveElement
     *
     * @return mixed
     */
    public function activeElement()
    {
        $result = $this->curl('POST', '/element/active');

        return $this->webDriverElement($result['value']);
    }

    /**
     * touch method chaining, e.g.,
     * - $session->touch()->method()
     *
     * @return \WebDriver\Touch
     *
     */
    public function touch()
    {
        return new Touch($this->url . '/touch');
    }

    /**
     * local_storage method chaining, e.g.,
     * - $session->local_storage()->method()
     *
     * @return \WebDriver\Storage
     */
    public function local_storage()
    {
        return Storage::factory('local', $this->url . '/local_storage');
    }

    /**
     * session_storage method chaining, e.g.,
     * - $session->session_storage()->method()
     *
     * @return \WebDriver\Storage
     */
    public function session_storage()
    {
        return Storage::factory('session', $this->url . '/session_storage');
    }

    /**
     * application cache chaining, e.g.,
     * - $session->application_cache()->status()
     *
     * @return \WebDriver\ApplicationCache
     */
    public function application_cache()
    {
        return new ApplicationCache($this->url . '/application_cache');
    }

    /**
     * log methods: /session/:sessionId/log (POST)
     * - $session->log($type) - get log for given log type
     * - $session->log()->method() - chaining
     *
     * @internal WD:getLog
     *
     * @return mixed
     */
    public function log()
    {
        // get log for given log type
        if (func_num_args() === 1) {
            $arg = func_get_arg(0);

            if (is_string($arg)) {
                $arg = array(
                    'type' => $arg,
                );
            }

            $result = $this->curl('POST', '/log', $arg);

            return $result['value'];
        }

        // chaining
        return new Log($this->url . '/log');
    }

    /**
     * {@inheritdoc}
     */
    protected function getElementPath($elementId)
    {
        return sprintf('%s/element/%s', $this->url, $elementId);
    }
}
