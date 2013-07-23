<?php
interface Yamp_Session_Model_Handler_Handler
{
	/**
	 * constructor - called before session is used
	 * @param string $savePath
	 * @param string $sessionName
	 * @return bool
	 */
	public function open($savePath, $sessionName);

	/**
	 * destructor - called at the session end
	 * @return bool
	 */
	public function close();

	/**
	 * read session data by id
	 * @param string $sessionId
	 * @return string
	 */
	public function read($sessionId);

	/**
	 * write session data for id
	 * @param string $sessionId
	 * @param string $sessionData
	 * @return bool
	 */
	public function write($sessionId, $sessionData);

	/**
	 * destroy a session entry
	 * @param string $sessionId
	 * @return bool
	 */
	public function destroy($sessionId);

	/**
	 * randomly called garbage collector
	 * @param integer $lifetime
	 * @return bool
	 */
	public function gc($lifetime);
}