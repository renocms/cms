export function getPackageBuildModuleUrl(packageName, relativeModulePath) {
    const normalizedPath = relativeModulePath
        .replace(/^\//u, '')
        .replace(/\.(vue|js)$/u, '.js');

    return `/vendor/${packageName}/build/${normalizedPath}`;
}

export function getCmsBuildModuleUrl(relativeModulePath) {
    return getPackageBuildModuleUrl('reno/cms', relativeModulePath);
}

export function getPackageAssetUrl(packageName, relativeAssetPath) {
    const normalizedPath = relativeAssetPath.replace(/^\//u, '');

    return `/vendor/${packageName}/${normalizedPath}`;
}

export function getCmsAssetUrl(relativeAssetPath) {
    return getPackageAssetUrl('reno/cms', relativeAssetPath);
}
