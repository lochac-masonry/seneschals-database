<?php

namespace Application;

use Interop\Container\ContainerInterface;
use Zend\Authentication;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\{Sql, Update};
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthenticationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $db = $container->get(AdapterInterface::class);

        $authService = new Authentication\AuthenticationService();

        $authService->setAdapter(new Authentication\Adapter\DbTable\CallbackCheckAdapter(
            $db,
            'users',
            'username',
            'hashed_password',
            function ($hashedPassword, $passwordInput) use ($db, $authService) {
                if (!password_verify($passwordInput, $hashedPassword)) {
                    return false;
                }

                // Password is correct - check if it needs rehashing (e.g. when hash algorithm or options change).
                if (password_needs_rehash($hashedPassword, PASSWORD_DEFAULT)) {
                    $db->query(
                        (new Sql($db))->buildSqlString(
                            (new Update('users'))
                                ->set(['hashed_password' => password_hash($passwordInput, PASSWORD_DEFAULT)])
                                ->where(['username' => $authService->getAdapter()->getIdentity()])
                        ),
                        $db::QUERY_MODE_EXECUTE
                    );
                }

                return true;
            }
        ));

        return $authService;
    }
}
