# Contributing

The plugin is released under the GNU General Public License v2, or later, and follows a standard GitHub development
process. We use the GitHub tracker for issues, and merge pull requests into the `alpha` branch, from which they are
promoted to `beta` and `main`.

## Project Structure

The plugin is `smarter-chat/`, as WordPress installs it: its main file, one class per concern in `includes/`, the
block in `blocks/chat/`, and `readme.txt`. The tests are in `tests/`. The architecture decisions are in
[doc/adr/](../doc/adr/), and [CLAUDE.md](../CLAUDE.md) and [skills/](../skills/) describe how the repository is
developed, for people and for AI coding agents.

## How to Contribute

1. **Fork the Repository**, and clone it locally. Run `make init`.
2. **Create a Branch** for each issue you work on.
3. **Make Your Changes**, following an existing pattern and the ADRs. A feature of the chat itself belongs in
   [@smarter.sh/ui-chat](https://github.com/smarter-sh/smarter/tree/main/smarter/react/packages/smarter.sh-chat). Add tests, and run `make lint test smoke`.
4. **Commit Your Changes** with [Conventional Commits](https://www.conventionalcommits.org/) messages, which
   semantic-release reads to version the plugin. The pre-commit hooks check the code and the messages.
5. **Push Your Changes** to your fork, and **Submit a Pull Request**.

## Code of Conduct

We expect all contributors to follow our [Code of Conduct](../CODE_OF_CONDUCT.md).
