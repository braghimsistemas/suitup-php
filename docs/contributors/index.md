title: How to contribute

# How to contribute

Everyone is welcome to contribute with _Suitup PHP Framework_. Just fork it, make your changes as minimal as possible and create a _pull request_. We ask to create minimal changes because by this way it's easier to merge your request to the project in approve case.

## Development Tools

As you want to contribute with the project there are mainly three projects that you must to know:

  * Obviouslly [Suitup PHP Framework](https://github.com/braghimsistemas/suitup-php)
  * [Suitup Skeleton](https://github.com/braghimsistemas/suitup-skeleton) - a sample project
  * [Suitup Dev](https://github.com/braghimsistemas/suitup2-dev) - An entire development environment project (always work in progress)

## Update the documentation

To properly create documentation for Suitup you will need [MkDocs](https://www.mkdocs.org/) which is a very simple way to create docs and publish with Github pages.

### Get Python 3

Download and install python 3, there's no secrets here.

### Download and install

  Get `pip` [here](https://pip.readthedocs.io/en/stable/installing/)

    $ python3.5 ~/Downloads/get-pip.py

!!! note ""
    If the outputs of the command `$ pip --version` is somethings like `pip 19.2.2 from /usr/local/lib/python3.5/dist-packages/pip (python 3.5)` **(note python 3.5)** so you just need to run the command `pip` instead of `pip3.5`

### Install [mkdocs](https://www.mkdocs.org/)

    $ pip3.5 install mkdocs

### Material Theme

    $ php3.5 install mkdocs-material

### mkdocs Server

    $ cd /path/to/suitup/root/
    $ mkdocs serve

## Deploy to Github pages

After work have been done deploy your changes to the `gh-pages`

See some more information about it [here](https://www.mkdocs.org/user-guide/deploying-your-docs/)

    $ mkdocs gh-deploy

-----

## Learn how to make it better

Our documentation was made under MkDocs with Material Theme, to learn more about mkdocs [click here](https://www.mkdocs.org/) and to get the best of Material Theme [click here](https://squidfunk.github.io/mkdocs-material/).

[CodeHilite][1] is an extension that adds syntax highlighting to code blocks
and is included in the standard Markdown library. The highlighting process is
executed during compilation of the Markdown file.

!!! failure "Syntax highlighting not working?"

    Please ensure that [Pygments][2] is installed. See the next section for
    further directions on how to set up Pygments or use the official
    [Docker image][3] with all dependencies pre-installed.

  [1]: https://python-markdown.github.io/extensions/code_hilite/
  [2]: http://pygments.org
  [3]: https://hub.docker.com/r/squidfunk/mkdocs-material/

##### Installation

CodeHilite parses code blocks and wraps them in `pre` tags. If [Pygments][2]
is installed, which is a generic syntax highlighter with support for over
[300 languages][4], CodeHilite will also highlight the code block. Pygments can
be installed with the following command:

[4]: https://pygments.org/languages/

``` sh
pip install pygments
```

[See more here](https://squidfunk.github.io/mkdocs-material/extensions/codehilite/#codehilite)
