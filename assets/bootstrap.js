import {startStimulusApp} from '@symfony/stimulus-bundle';


import {Application} from "@hotwired/stimulus";

import AddClassController from "./controllers/add_class_controller.js";

const application = Application.start();
application.register("add-class", AddClassController);
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
