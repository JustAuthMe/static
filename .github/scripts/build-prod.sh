rm -rf html.new/.github html.new/.git html.new/.gitignore html.new/README.md
echo "$TAG" > html.new/version.txt
touch release.tar.gz
tar --exclude=release.tar.gz -zcvf release.tar.gz httpdocs.new
