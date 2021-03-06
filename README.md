**Dump-Crawler for MoneyPlace**
---

## Installation

You’ll start by cloning this repo into your sites folder.

1. Click **Clone** on the right side.
2. Click the *copy to clipboard* icon.
3. In iTerm (or your preferred terminal) ```cd``` to your *sites* folder.
4. Paste the *copied* text and run the command.
5. ```cd``` into the folder (*dump-crawler*) and run ```composer install```

Next you’ll add these lines to your *.zshrc* (or *.bash_profile*) file:
```shell
# remove trailing dd's
function cleanup() {
	projectPath="${PWD}"
	cd ~/Sites/dump-crawler && ./dump clean "${projectPath}" && cd "${projectPath}"
}
```

After changing those lines do not forget to restart your terminal (or run the ```source``` command).

**NOTE:** if your *sites* folder is located in a different place, then update the path accordingly.


---

## Running var_dump search

In your terminal, and within the project you would like to crawl, run the command ```cleanup```.
