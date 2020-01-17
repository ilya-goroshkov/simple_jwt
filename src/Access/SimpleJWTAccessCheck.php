<?php

namespace Drupal\simple_jwt\Access;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks access for displaying configuration translation page.
 */
class SimpleJWTAccessCheck implements AccessInterface {

  /**
   * Drupal core Request Stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $request;

  /**
   * JWT from module configuration.
   */
  private $jwt;

  /**
   * CustomAccessCheck constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Drupal core Request Stack.
   */
  public function __construct(RequestStack $request, ConfigFactoryInterface $configFactory) {
    $this->request = $request->getCurrentRequest();
    $this->jwt = $configFactory->get('simple_jwt.config')->get('jwt');
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    $jwt = $this->getJwtFromRequest($this->request);
    if ($jwt == $this->jwt) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Gets a raw JsonWebToken from the current request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return string|bool
   *   Raw JWT String if on request, false if not.
   */
  protected function getJwtFromRequest(Request $request) {
    $auth_header = $request->headers->get('Authorization');
    $matches = array();
    if (!$hasJWT = preg_match('/^Bearer (.*)/', $auth_header, $matches)) {
      return FALSE;
    }

    return $matches[1];
  }

}
