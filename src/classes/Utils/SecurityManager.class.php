<?php

class SecurityManager
{
    const SESSION_USER_VAR      = 'auth-user-id';
    
    protected static $user = null;
    
    public static function auth(HttpRequest $request)
    {
        if (Session::exist(self::SESSION_USER_VAR) && Session::get(self::SESSION_USER_VAR)) {
            try {
                $user = User::dao()->getById(Session::get(self::SESSION_USER_VAR));
                if ($user instanceof User && !$user->isBan()) {
                    self::$user = $user;
                }
            } catch (ObjectNotFoundException $e) { }
        }
    }
    
    public static function setUser(User $user)
    {
        Session::assign(self::SESSION_USER_VAR, $user->getId());
    }
    
    public static function logout()
    {
        Session::drop(self::SESSION_USER_VAR);
    }
    
    public static function isAuth()
    {
        return SecurityManager::$user instanceof User;
    }
    
    public static function getUser()
    {
        return SecurityManager::$user;
    }
}