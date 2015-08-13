function result = SeparationIndex( data, clusterinfo )
% SeparationIndex - calculate the Separation Index

ctype = unique(clusterinfo);
n = length(ctype);
d = [];
s = 0;

for i = 1:n
    idxi = find(clusterinfo == ctype(i));
    for j = i+1:n
        idxj = find(clusterinfo == ctype(j));
        dmatrix = pdist2(data(idxi,:),data(idxj,:),'euclidean');
        d = [d max(max(dmatrix))];
    end
    mi = mean(data(idxi,:));
    num = length(idxi);
    for k = 1:num
        s = s + sum((data(idxi(k),:)-mi).^2); % Euclidean Distance
    end
end

dmin = min(d);
result = s / (length(clusterinfo)*dmin);