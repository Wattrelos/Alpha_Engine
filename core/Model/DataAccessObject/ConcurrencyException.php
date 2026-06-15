<?php

namespace Alpha\Model\DataAccessObject;

/**
 * Exception ConcurrencyException
 * 
 * Lançada quando ocorre uma colisão de modificação simultânea (bloqueio otimista).
 */
class ConcurrencyException extends \Exception
{
}
