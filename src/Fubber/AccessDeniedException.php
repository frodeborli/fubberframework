<?php
namespace Fubber;
/**
*	Exception to be used whenever a user tries to perform an illegal operation, for example
*	if the user is not authenticated.
*/
class AccessDeniedException extends Exception {
}
