1. To setup the project locally:
````
docker-compose up -d
docker-compose exec app composer install
````

You could address the project by http://imageapp.local, just update your local hosts file with a map 127.0.0.1 imageapp.local.

2. To run the tests:
````
docker-compose up -d
docker-compose exec app ./vendor/bin/phpunit -c phpunit.xml
````

Endpoints:

1. GET http://imageapp.local/{image_filename}

Try to get the available image. Searches both for a modified and original images.

2. GET http://imageapp.local/{image_filename}/crop?width=100&height=100

Crop available image to the defined size. You cannot modify already modified image.

3. GET http://imageapp.local/{image_filename}/resize?width=100&height=100

Resize available image to the defined size. You cannot modify already modified image.

4. GET http://imageapp.local/show

Shows html page with resized and cropped images samples.

Check _src/routes/app.php_ for the available routes.

You may try this example to check how everything works:

````
GET http://imageapp.local/dog.webp/crop?width=200&height=200
````