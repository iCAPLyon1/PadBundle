var $addLink = jQuery('<a href="#" class="btn btn-info"><span class="glyphicon glyphicon-plus"></span></a>');
        var $newLink = jQuery('<div class="add-link"></div>').append($addLink);
        var collectionHolder = jQuery('.icap_padbundle_pad');

        jQuery(document).ready(function() {
            collectionHolder.append($newLink);
            $addLink.on('click', function(e) {
                e.preventDefault();
                addForm(collectionHolder, $newLink);
            });

            collectionHolder.find('.paduser').each(function() {
                addFormDeleteLink($(this));
            });
        });

        function addForm(collectionHolder, $newLink) {
            var prototype = collectionHolder.data('prototype');
            var $newForm = prototype
                .replace(/__name__label__/g, 'mail')
                .replace(/__name__/g, collectionHolder.children().length)
                .replace(/input type="email"/g, 'input type="email" placeholder="email"')
            ;
            var $newFormDiv = jQuery('<div></div>').append($newForm);
            $newLink.before($newFormDiv);
            addFormDeleteLink($newFormDiv);
        }

        function addFormDeleteLink($formDiv) {
            var $removeFormA = jQuery('<div class="remove-link"><a class="btn btn-danger" href="#"><span class="glyphicon glyphicon-minus"></span></a></div>');
            $formDiv.append($removeFormA);

            $removeFormA.find('a').on('click', function(e) {
                e.preventDefault();
                $formDiv.remove();
            });
        }