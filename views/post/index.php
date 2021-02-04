<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <title>Challenge</title>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <form action="<?php echo $config->get('app.baseUrl') ?>/index.php" class="mb-4" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control<?php echo $validator->has('title') ? ' is-invalid' : '' ?>" id="title" name="title" placeholder="Input title" value="<?php echo $validator->old('title') ?>">
                        <?php if ($validator->has('title')) : ?>
                            <div class="invalid-feedback">
                                <?php echo $validator->get('title') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Body</label>
                        <textarea class="form-control<?php echo $validator->has('body') ? ' is-invalid' : '' ?>" id="body" name="body" rows="3" placeholder="Input body"><?php echo $validator->old('body') ?></textarea>
                        <?php if ($validator->has('body')) : ?>
                            <div class="invalid-feedback">
                                <?php echo $validator->get('body') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-primary" type="submit" name="submit">Submit</button>
                </form>
        
                <?php foreach ($posts as $post) : ?>
                    <div class="row mb-2">
                        <div class="card col" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $post->title ?></h5>
                                <p class="card-text mb=3"><?php echo $post->body ?></p>
                                <p class="card-text">
                                    <small>
                                        <?php echo $post->created_at->format('d-m-Y H:i') ?>
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</body>
</html>