#!bash

# copy config
if [ ! -f "$(pwd)/bash/config.sh" ]; then
    cp "$(pwd)/bash/config.sample.sh" "$(pwd)/bash/config.sh"
fi

# init composer if needed
. $(pwd)"/bash/composer.sh"
