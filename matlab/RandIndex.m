function result = RandIndex(gnd,clusterinfo)
% RandIndex - calculates Rand Indices to compare two partitions

if nargin < 2 | min(size(gnd)) > 1 | min(size(clusterinfo)) > 1
   error('RandIndex: Requires two vector arguments')
   return
end

ng = length(gnd);
nc = length(clusterinfo);

if ng~=nc
    disp('These two vector should be in same length');
    return
end

m = ng*(ng - 1)/2;
Iu = zeros(1,m);
Iv = zeros(1,m);

k = 1;
for i = 1:ng
    for j = (i+1):ng
        Iu(k) = (gnd(i)==gnd(j));           % for class label
        Iv(k) = (clusterinfo(i)==clusterinfo(j));     % for clustering result
        k = k+1;
    end
end

a = sum(Iu&Iv);
d = sum(~(Iu|Iv));

result = (a+d)/m;

end

