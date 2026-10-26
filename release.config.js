// semantic-release versions the plugin from the conventional commit messages (doc/adr/011-versioning.md), with the
// same rules and release notes as Smarter's other repositories. It runs on each push to main, and to alpha and beta,
// whose releases are prereleases (.github/workflows/pushMain.yml).
//
// The release writes its version into the plugin's header, SMARTER_CHAT_VERSION and readme.txt's Stable tag, builds
// build/smarter-chat-<version>.zip with the latest embed script (which writes SMARTER_CHAT_EMBED_VERSION), smoke tests
// it in WordPress, attaches it to the GitHub release, and commits the versions. pushMain.yml then deploys a release on main to the WordPress.org plugin directory (doc/adr/010-ci-cd.md).
const setVersion = [
  "sed -i -E 's/^( \\* Version: +).*/\\1${nextRelease.version}/' smarter-chat/smarter-chat.php",
  "sed -i -E \"s/^define\\( 'SMARTER_CHAT_VERSION', '[^']+' \\);/define( 'SMARTER_CHAT_VERSION', '${nextRelease.version}' );/\" smarter-chat/smarter-chat.php",
  "sed -i -E 's/^(Stable tag: +).*/\\1${nextRelease.version}/' smarter-chat/readme.txt",
  'sed -i -E \'s/"version": "[^"]+"/"version": "${nextRelease.version}"/\' smarter-chat/blocks/chat/block.json',
  "sed -i -E \"s/'version'( +)=> '[^']+'/'version'\\1=> '${nextRelease.version}'/\" smarter-chat/blocks/chat/edit.asset.php",
  "npm version --no-git-tag-version --allow-same-version ${nextRelease.version}",
].join(" && ");

export default {
  branches: ["main", { name: "beta", prerelease: true }, { name: "alpha", prerelease: true }],
  plugins: [
    [
      "@semantic-release/commit-analyzer",
      {
        preset: "conventionalcommits",
        releaseRules: [
          { type: "docs", release: false },
          { type: "test", release: false },
          { type: "style", release: false },
          { type: "refactor", release: false },
        ],
        parserOpts: { noteKeywords: ["BREAKING CHANGE", "BREAKING CHANGES"] },
      },
    ],
    [
      "@semantic-release/release-notes-generator",
      {
        preset: "conventionalcommits",
        presetConfig: {
          types: [
            { type: "feat", section: "Features", hidden: false },
            { type: "fix", section: "Bug Fixes", hidden: false },
            { type: "refactor", section: "Refactoring", hidden: false },
            { type: "perf", section: "Performance", hidden: false },
          ],
        },
      },
    ],
    [
      "@semantic-release/changelog",
      {
        changelogFile: "CHANGELOG.md",
        changelogTitle: `# Change Log\n\nAll notable changes to this project will be documented in this file.\n\nThe format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).\n\n`,
      },
    ],
    ["@semantic-release/exec", { prepareCmd: `${setVersion} && scripts/dist.sh && tests/smoke/run.sh` }],
    [
      "@semantic-release/github",
      { assets: [{ path: "build/smarter-chat-*.zip", label: "smarter-chat.zip (install this)" }] },
    ],
    [
      "@semantic-release/git",
      {
        assets: [
          "CHANGELOG.md",
          "package.json",
          "package-lock.json",
          "smarter-chat/smarter-chat.php",
          "smarter-chat/readme.txt",
          "smarter-chat/blocks/chat/block.json",
          "smarter-chat/blocks/chat/edit.asset.php",
        ],
        message: "chore(release): ${nextRelease.version} [skip ci]\n\n${nextRelease.notes}",
      },
    ],
  ],
};
