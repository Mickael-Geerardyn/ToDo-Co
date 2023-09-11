# Comment participer au projet
==========================================

## Règles à respecter et processus de qualité


### Règles (standards) à respecter
---------------------------------

*Ressource :* 
-	https://symfony.com/doc/5.4/contributing/code/standards.html 
-	https://www.php-fig.org/psr/psr-1/
-	https://www.php-fig.org/psr/psr-2/ 
-	https://www.php-fig.org/psr/psr-4/
-	https://www.php-fig.org/psr/psr-12/ 

Des standards de programmation sont recommandés par Symfony et sont listés dans la documentation, sur la page prévue à cet effet (Premier lien).

Il est important de se référer à ces standards afin que chaque développeur travaillant sur le projet ou reprenant celui-ci puisse lire et comprendre la logique avec facilité et l’inviter implicitement à suivre ces bonnes pratiques.

Suivre ces bonnes pratiques permet d’améliorer la qualité de l’application, 
le travail collaboratif, réduire la dette technique et améliorer la 
maintenabilité du projet.

Symfony suit également les standards des PSR-1, PSR-2, PSR-4 et PSR-12 (PSR = PHP Standards Recommendations) qui sont des fondamentaux de la programmation avec PHP, langage sur lequel Symfony est basé.

Le PSR-2 est déclaré déprécié par la documentation officielle. Le PSR-12 en est l’alternative recommandée.

Je vous invite cependant à prendre connaissance des deux standards afin de comprendre les modifications apportées par le PSR-12. En effet, le PSR-12 a été mis en place afin d’adapter les standards du PSR-2 qui peuvent être sujets à interprétation relativement contexte plus moderne de programmation.


***Processus de qualité***

1. Tests unitaires

Après avoir programmé des méthodes, il est important d’implémenter des tests unitaires afin de vérifier le bon fonctionnement de chaque méthode de façon isolé.
C’est-à-dire vérifier que ce que la méthode renvoi correspond à ce que l’on attends d’elle.
Par exemple, une méthode qui doit renvoyer le titre d’une tâche, vérifier que celle-ci renvoi bien le titre inséré via un test unitaire.

Comme précisé, les tests unitaires servent à tester une fonctionnalité de 
façon isolée, en dehors de l’ensemble du cheminement par lequel elle serait amenée à être utilisée. (Par exemple, lors de l’affichage des tâches sur la page, la méthode est appelé après que le controller ait demandé à recevoir l’ensemble des tâches qu’ils a ensuite passé à la vue).


2. Tests fonctionnels

Les tests fonctionnels servent quant à eux à tester une fonctionnalité 
complète de l’application, par exemple modifier une tâche.

En effet, pour arriver à l’enregistrement de la modification de la tâche, l’utilisateur et l’application passent plusieurs cheminements.

Cela commence par l’authentification de l’utilisateur, le clic sur le lien pour accéder à la page listant les tâches, le clic sur le bouton amenant à la page du formulaire de modification de la tâche sélectionnée, le clic sur le bouton de soumission du formulaire puis la redirection de l’utilisateur sur la page de la liste des tâches avec un message de succès par exemple.
Le test fonctionnel va servir à simuler ce cheminement à travers l’application afin de tester l’ensemble des fonctionnalités nécessaires à sa réalisation pour vérifier que l’application se comporte comme souhaité.

Ces tests sont un prérequis en terme d’assurance qualité de code. Ils permettent de vérifier en amont et de façon automatique l’ensemble des fonctionnalités isolées puis ces fonctionnalités intégrées dans un cheminement utilisateur.




### Règles collaboratives
-------------------------

Afin d'assurer une qualité à l'application et à son fonctionnement, il est demandé d'utiliser le système de contrôle de versions à l'aide gds commandes `git` et de github.

Chaque nouvelle fonctionnalité ou chaque modification de fonctionnalité doit être effectué sur une nouvelle branche, jamais immédiatement sur la branche "main" du repository.

En effet, la branche "main" est la branche qui comprends l'ensemble du code et des fonctionnalités considérées comme validées et donc qui font parti du projet qui est mis en production.

A chaque nouvel ajout ou modification, il est demandé de créer une pull request sur github afin que le travail puisse être vérifié et validé par un ou plusieurs autres développeurs.

Ce processus vise à garantir une qualité maximale mais aussi apporter un oeil nouveau sur les ajouts et modifications dans le but de d'assurer que le code ne possède pas de bug et/ou de failles de sécurité.

Ce travail collaboratif est un prérequis sur lequel il ne faut en aucun cas faire l'impasse.
