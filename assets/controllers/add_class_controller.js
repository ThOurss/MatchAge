import {Controller} from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */

export default class extends Controller {
    static targets = ['target'];

    connect() {
        console.log('Controller connecté');
    }

    addClass(event) {
        event.preventDefault();

        // Retirer la classe de tous les éléments (si nécessaire)
        this.targetTargets.forEach(element => {
            element.classList.remove('active'); // Remplacez 'active' par la classe que vous voulez gérer
        });

        // Ajouter la classe au bon élément
        const targetId = event.currentTarget.dataset.targetId; // Récupère l'ID de l'élément cible
        const targetElement = this.targetTargets.find(el => el.id === targetId);
        if (targetElement) {
            targetElement.classList.add('active'); // Remplacez 'active' par la classe souhaitée
        }
    }

    removeClass(event) {
        const element = event.currentTarget;

        // Ajouter la classe à l'élément
        element.classList.remove("test");

        // Optionnel : Afficher un message dans la console pour vérifier que l'événement est bien traité
        console.log("Classe retirée : ", element.className);
    }

    toggleClass(event) {
        event.preventDefault();

        // Retirer la classe de tous les éléments (si nécessaire)


        // Ajouter la classe au bon élément
        const targetId = event.currentTarget.dataset.targetId; // Récupère l'ID de l'élément cible
        const targetElement = this.targetTargets.find(el => el.id === targetId);

        targetElement.classList.toggle('search-actif'); // Remplacez 'active' par la classe souhaitée

    }
}
