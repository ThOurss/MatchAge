import {Controller} from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */

export default class extends Controller {
    static targets = [];

    connect() {
        console.log('Controller connecté');
    }

    addClass(event) {
        // Récupérer l'élément actuel qui a déclenché l'événement
        const element = event.currentTarget;

        // Ajouter la classe à l'élément
        element.classList.add("test");

        // Optionnel : Afficher un message dans la console pour vérifier que l'événement est bien traité
        console.log("Classe ajoutée : ", element.className);
    }

    removeClass(event) {
        const element = event.currentTarget;

        // Ajouter la classe à l'élément
        element.classList.remove("test");

        // Optionnel : Afficher un message dans la console pour vérifier que l'événement est bien traité
        console.log("Classe retirée : ", element.className);
    }

    toggleClass(event) {
        const element = event.currentTarget;
        element.classList.toggle("active");
        console.log('Événement click appelé');
    }
}
